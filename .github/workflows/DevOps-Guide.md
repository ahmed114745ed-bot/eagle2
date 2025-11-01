# 🚀 DevOps Implementation Guide - Voice Platform
## مشروع منصة الصوت الحي (Voice Chat Rooms)

---

## 📋 نظرة عامة على المشروع

### المواصفات:
- **2000+ غرفة صوتية نشطة**
- **410 مستخدم لكل غرفة** (10 متحدثين + 400 مستمع)
- **~820,000 اتصال متزامن**
- **Real-time audio streaming**
- **Low latency (<150ms)**

### التقنيات الأساسية:
- **Google Cloud Platform (GCP)**
- **Kubernetes (GKE)**
- **Mediasoup (SFU for audio)**
- **Node.js + TypeScript**
- **PostgreSQL + Redis**

---

## 🎯 المهام المطلوبة من فريق DevOps

---

## Phase 1: GCP Infrastructure Setup

### 1.1 إنشاء وتجهيز GCP Project

```bash
# إنشاء مشروع جديد
gcloud projects create voice-platform-prod --name="Voice Platform Production"

# تفعيل Billing
gcloud beta billing projects link voice-platform-prod \
  --billing-account=YOUR_BILLING_ACCOUNT_ID

# Set default project
gcloud config set project voice-platform-prod
```

### 1.2 تفعيل GCP APIs المطلوبة

```bash
# تفعيل الخدمات الأساسية
gcloud services enable \
  container.googleapis.com \
  compute.googleapis.com \
  sqladmin.googleapis.com \
  redis.googleapis.com \
  cloudresourcemanager.googleapis.com \
  cloudbuild.googleapis.com \
  containerregistry.googleapis.com \
  monitoring.googleapis.com \
  logging.googleapis.com \
  secretmanager.googleapis.com \
  cloudkms.googleapis.com \
  servicenetworking.googleapis.com \
  vpcaccess.googleapis.com
```

### 1.3 إعداد Networking

```bash
# إنشاء VPC مخصص
gcloud compute networks create voice-platform-vpc \
  --subnet-mode=custom \
  --bgp-routing-mode=global

# إنشاء Subnets للمناطق المختلفة
# US East
gcloud compute networks subnets create voice-us-east1 \
  --network=voice-platform-vpc \
  --region=us-east1 \
  --range=10.1.0.0/20 \
  --enable-private-ip-google-access \
  --enable-flow-logs

# Europe West
gcloud compute networks subnets create voice-eu-west1 \
  --network=voice-platform-vpc \
  --region=eu-west1 \
  --range=10.2.0.0/20 \
  --enable-private-ip-google-access \
  --enable-flow-logs

# Secondary ranges للـ GKE pods & services
gcloud compute networks subnets update voice-us-east1 \
  --region=us-east1 \
  --add-secondary-ranges pods=10.4.0.0/14,services=10.8.0.0/20

gcloud compute networks subnets update voice-eu-west1 \
  --region=eu-west1 \
  --add-secondary-ranges pods=10.12.0.0/14,services=10.16.0.0/20
```

### 1.4 إعداد Cloud NAT (للخروج للإنترنت)

```bash
# إنشاء Cloud Router
gcloud compute routers create voice-router-us-east1 \
  --network=voice-platform-vpc \
  --region=us-east1

# إنشاء Cloud NAT
gcloud compute routers nats create voice-nat-us-east1 \
  --router=voice-router-us-east1 \
  --region=us-east1 \
  --nat-all-subnet-ip-ranges \
  --auto-allocate-nat-external-ips
```

### 1.5 Firewall Rules

```bash
# السماح بـ internal traffic
gcloud compute firewall-rules create allow-internal \
  --network=voice-platform-vpc \
  --allow=tcp,udp,icmp \
  --source-ranges=10.0.0.0/8

# السماح بـ SSH للصيانة (من IP محدد)
gcloud compute firewall-rules create allow-ssh \
  --network=voice-platform-vpc \
  --allow=tcp:22 \
  --source-ranges=YOUR_OFFICE_IP/32

# السماح بـ WebRTC ports
gcloud compute firewall-rules create allow-webrtc \
  --network=voice-platform-vpc \
  --allow=udp:10000-60000 \
  --source-ranges=0.0.0.0/0
```

---

## Phase 2: Terraform Infrastructure as Code

### 2.1 هيكل Terraform المطلوب

```
infrastructure/
├── terraform/
│   ├── environments/
│   │   ├── dev/
│   │   │   ├── main.tf
│   │   │   ├── variables.tf
│   │   │   └── terraform.tfvars
│   │   ├── staging/
│   │   └── production/
│   ├── modules/
│   │   ├── gke/
│   │   │   ├── main.tf
│   │   │   ├── variables.tf
│   │   │   └── outputs.tf
│   │   ├── cloudsql/
│   │   ├── redis/
│   │   ├── networking/
│   │   ├── monitoring/
│   │   └── secrets/
│   └── backend.tf
```

### 2.2 Terraform Backend Configuration

```hcl
# backend.tf
terraform {
  backend "gcs" {
    bucket = "voice-platform-terraform-state"
    prefix = "terraform/state"
  }
  
  required_version = ">= 1.5.0"
  
  required_providers {
    google = {
      source  = "hashicorp/google"
      version = "~> 5.0"
    }
    kubernetes = {
      source  = "hashicorp/kubernetes"
      version = "~> 2.23"
    }
  }
}

provider "google" {
  project = var.project_id
  region  = var.region
}
```

### 2.3 GKE Cluster Module (Example)

```hcl
# modules/gke/main.tf
resource "google_container_cluster" "primary" {
  name     = var.cluster_name
  location = var.region
  
  # Private cluster
  private_cluster_config {
    enable_private_nodes    = true
    enable_private_endpoint = false
    master_ipv4_cidr_block = "172.16.0.0/28"
  }
  
  # Network configuration
  network    = var.network
  subnetwork = var.subnetwork
  
  ip_allocation_policy {
    cluster_secondary_range_name  = "pods"
    services_secondary_range_name = "services"
  }
  
  # Remove default node pool
  remove_default_node_pool = true
  initial_node_count       = 1
  
  # Enable Workload Identity
  workload_identity_config {
    workload_pool = "${var.project_id}.svc.id.goog"
  }
  
  # Maintenance window
  maintenance_policy {
    daily_maintenance_window {
      start_time = "03:00"
    }
  }
  
  # Monitoring
  monitoring_config {
    enable_components = ["SYSTEM_COMPONENTS", "WORKLOADS"]
    managed_prometheus {
      enabled = true
    }
  }
  
  # Logging
  logging_config {
    enable_components = ["SYSTEM_COMPONENTS", "WORKLOADS"]
  }
  
  # Security
  master_auth {
    client_certificate_config {
      issue_client_certificate = false
    }
  }
}

# Node pool للـ API Services
resource "google_container_node_pool" "api_pool" {
  name       = "api-pool"
  location   = var.region
  cluster    = google_container_cluster.primary.name
  
  initial_node_count = 3
  
  autoscaling {
    min_node_count = 3
    max_node_count = 20
  }
  
  node_config {
    machine_type = "n2-standard-4"
    disk_size_gb = 100
    disk_type    = "pd-ssd"
    
    oauth_scopes = [
      "https://www.googleapis.com/auth/cloud-platform"
    ]
    
    labels = {
      workload = "api"
    }
    
    taint {
      key    = "workload"
      value  = "api"
      effect = "NO_SCHEDULE"
    }
    
    workload_metadata_config {
      mode = "GKE_METADATA"
    }
  }
}

# Node pool للـ Media Servers
resource "google_container_node_pool" "media_pool" {
  name       = "media-pool"
  location   = var.region
  cluster    = google_container_cluster.primary.name
  
  initial_node_count = 5
  
  autoscaling {
    min_node_count = 5
    max_node_count = 100
  }
  
  node_config {
    machine_type = "c2-standard-8"
    disk_size_gb = 200
    disk_type    = "pd-ssd"
    
    oauth_scopes = [
      "https://www.googleapis.com/auth/cloud-platform"
    ]
    
    labels = {
      workload = "media"
    }
    
    taint {
      key    = "workload"
      value  = "media"
      effect = "NO_SCHEDULE"
    }
    
    workload_metadata_config {
      mode = "GKE_METADATA"
    }
  }
}
```

### 2.4 Cloud SQL Module

```hcl
# modules/cloudsql/main.tf
resource "google_sql_database_instance" "main" {
  name             = var.instance_name
  database_version = "POSTGRES_15"
  region           = var.region
  
  settings {
    tier              = "db-custom-8-32768"
    availability_type = "REGIONAL"  # High Availability
    disk_type         = "PD_SSD"
    disk_size         = 500
    disk_autoresize   = true
    
    backup_configuration {
      enabled                        = true
      start_time                     = "02:00"
      point_in_time_recovery_enabled = true
      transaction_log_retention_days = 7
      backup_retention_settings {
        retained_backups = 30
      }
    }
    
    ip_configuration {
      ipv4_enabled    = false
      private_network = var.network_id
      require_ssl     = true
    }
    
    database_flags {
      name  = "max_connections"
      value = "500"
    }
    
    database_flags {
      name  = "shared_buffers"
      value = "8388608"  # 8GB
    }
    
    insights_config {
      query_insights_enabled  = true
      query_string_length     = 1024
      record_application_tags = true
    }
  }
  
  deletion_protection = true
}

resource "google_sql_database" "database" {
  name     = var.database_name
  instance = google_sql_database_instance.main.name
}

resource "google_sql_user" "user" {
  name     = var.db_user
  instance = google_sql_database_instance.main.name
  password = var.db_password
}
```

### 2.5 Redis Module (Memorystore)

```hcl
# modules/redis/main.tf
resource "google_redis_instance" "cache" {
  name           = var.instance_name
  tier           = "STANDARD_HA"
  memory_size_gb = 50
  region         = var.region
  
  redis_version     = "REDIS_7_0"
  authorized_network = var.network_id
  
  redis_configs = {
    maxmemory-policy = "allkeys-lru"
    notify-keyspace-events = "Ex"
  }
  
  maintenance_policy {
    weekly_maintenance_window {
      day = "SUNDAY"
      start_time {
        hours   = 3
        minutes = 0
      }
    }
  }
}
```

---

## Phase 3: Kubernetes Setup & Configuration

### 3.1 Connect to GKE Cluster

```bash
# Get credentials
gcloud container clusters get-credentials voice-platform-cluster \
  --region=us-east1 \
  --project=voice-platform-prod

# Verify connection
kubectl cluster-info
kubectl get nodes
```

### 3.2 Namespace Structure

```yaml
# namespaces.yaml
apiVersion: v1
kind: Namespace
metadata:
  name: voice-api
  labels:
    name: voice-api
---
apiVersion: v1
kind: Namespace
metadata:
  name: voice-signaling
  labels:
    name: voice-signaling
---
apiVersion: v1
kind: Namespace
metadata:
  name: voice-media
  labels:
    name: voice-media
---
apiVersion: v1
kind: Namespace
metadata:
  name: voice-monitoring
  labels:
    name: voice-monitoring
---
apiVersion: v1
kind: Namespace
metadata:
  name: voice-ingress
  labels:
    name: voice-ingress
```

```bash
kubectl apply -f namespaces.yaml
```

### 3.3 Install Cert-Manager (للـ SSL Certificates)

```bash
# Install cert-manager
kubectl apply -f https://github.com/cert-manager/cert-manager/releases/download/v1.13.0/cert-manager.yaml

# Verify installation
kubectl get pods --namespace cert-manager
```

```yaml
# letsencrypt-issuer.yaml
apiVersion: cert-manager.io/v1
kind: ClusterIssuer
metadata:
  name: letsencrypt-prod
spec:
  acme:
    server: https://acme-v02.api.letsencrypt.org/directory
    email: devops@yourcompany.com
    privateKeySecretRef:
      name: letsencrypt-prod
    solvers:
    - http01:
        ingress:
          class: nginx
```

### 3.4 Install NGINX Ingress Controller

```bash
# Add Helm repo
helm repo add ingress-nginx https://kubernetes.github.io/ingress-nginx
helm repo update

# Install NGINX Ingress
helm install nginx-ingress ingress-nginx/ingress-nginx \
  --namespace voice-ingress \
  --set controller.replicaCount=3 \
  --set controller.nodeSelector."kubernetes\.io/os"=linux \
  --set controller.service.type=LoadBalancer \
  --set controller.service.externalTrafficPolicy=Local \
  --set controller.metrics.enabled=true \
  --set controller.podAnnotations."prometheus\.io/scrape"=true \
  --set controller.podAnnotations."prometheus\.io/port"=10254
```

### 3.5 Setup Secrets Management

```bash
# Create secrets from Secret Manager
gcloud secrets create db-password --data-file=- <<< "YOUR_DB_PASSWORD"
gcloud secrets create redis-password --data-file=- <<< "YOUR_REDIS_PASSWORD"
gcloud secrets create jwt-secret --data-file=- <<< "YOUR_JWT_SECRET"

# Create Kubernetes secrets
kubectl create secret generic db-credentials \
  --from-literal=password=$(gcloud secrets versions access latest --secret=db-password) \
  --namespace=voice-api

kubectl create secret generic jwt-secret \
  --from-literal=secret=$(gcloud secrets versions access latest --secret=jwt-secret) \
  --namespace=voice-api
```

### 3.6 ConfigMaps للإعدادات

```yaml
# api-config.yaml
apiVersion: v1
kind: ConfigMap
metadata:
  name: api-config
  namespace: voice-api
data:
  NODE_ENV: "production"
  LOG_LEVEL: "info"
  DB_HOST: "10.x.x.x"  # Cloud SQL private IP
  DB_PORT: "5432"
  DB_NAME: "voice_platform"
  REDIS_HOST: "10.x.x.x"  # Redis private IP
  REDIS_PORT: "6379"
  MAX_ROOMS_PER_SERVER: "50"
  MAX_SPEAKERS_PER_ROOM: "10"
  MAX_LISTENERS_PER_ROOM: "400"
```

---

## Phase 4: Monitoring & Logging Setup

### 4.1 Install Prometheus & Grafana

```bash
# Add Helm repos
helm repo add prometheus-community https://prometheus-community.github.io/helm-charts
helm repo add grafana https://grafana.github.io/helm-charts
helm repo update

# Install Prometheus Stack
helm install prometheus prometheus-community/kube-prometheus-stack \
  --namespace voice-monitoring \
  --set prometheus.prometheusSpec.retention=30d \
  --set prometheus.prometheusSpec.storageSpec.volumeClaimTemplate.spec.resources.requests.storage=100Gi \
  --set grafana.adminPassword=YOUR_ADMIN_PASSWORD \
  --set grafana.persistence.enabled=true \
  --set grafana.persistence.size=10Gi
```

### 4.2 Custom Metrics Configuration

```yaml
# servicemonitor-media.yaml
apiVersion: monitoring.coreos.com/v1
kind: ServiceMonitor
metadata:
  name: media-server-metrics
  namespace: voice-media
spec:
  selector:
    matchLabels:
      app: media-server
  endpoints:
  - port: metrics
    interval: 15s
    path: /metrics
```

### 4.3 Alerting Rules

```yaml
# prometheus-rules.yaml
apiVersion: monitoring.coreos.com/v1
kind: PrometheusRule
metadata:
  name: voice-platform-alerts
  namespace: voice-monitoring
spec:
  groups:
  - name: voice-platform
    interval: 30s
    rules:
    - alert: HighCPUUsage
      expr: avg(rate(container_cpu_usage_seconds_total[5m])) by (pod) > 0.8
      for: 5m
      labels:
        severity: warning
      annotations:
        summary: "High CPU usage detected"
        description: "Pod {{ $labels.pod }} CPU usage is above 80%"
    
    - alert: HighMemoryUsage
      expr: container_memory_usage_bytes / container_spec_memory_limit_bytes > 0.9
      for: 5m
      labels:
        severity: critical
      annotations:
        summary: "High memory usage detected"
    
    - alert: MediaServerDown
      expr: up{job="media-server"} == 0
      for: 2m
      labels:
        severity: critical
      annotations:
        summary: "Media server is down"
    
    - alert: HighAudioLatency
      expr: audio_processing_latency_ms > 150
      for: 5m
      labels:
        severity: warning
      annotations:
        summary: "Audio latency is high"
```

### 4.4 Grafana Dashboards

إنشاء dashboards للـ:
- **Cluster Overview**: CPU, Memory, Network
- **Media Servers**: Active rooms, connections, latency
- **API Performance**: Request rate, response time, errors
- **Database Metrics**: Connections, queries, locks
- **Redis Metrics**: Hit rate, memory usage

---

## Phase 5: CI/CD Pipeline Setup

### 5.1 Cloud Build Configuration

```yaml
# cloudbuild.yaml
steps:
  # Build Docker images
  - name: 'gcr.io/cloud-builders/docker'
    args:
      - 'build'
      - '-t'
      - 'gcr.io/$PROJECT_ID/voice-api:$SHORT_SHA'
      - '-t'
      - 'gcr.io/$PROJECT_ID/voice-api:latest'
      - '-f'
      - 'services/api/Dockerfile'
      - '.'
    id: 'build-api'
  
  - name: 'gcr.io/cloud-builders/docker'
    args:
      - 'build'
      - '-t'
      - 'gcr.io/$PROJECT_ID/voice-signaling:$SHORT_SHA'
      - '-t'
      - 'gcr.io/$PROJECT_ID/voice-signaling:latest'
      - '-f'
      - 'services/signaling/Dockerfile'
      - '.'
    id: 'build-signaling'
  
  - name: 'gcr.io/cloud-builders/docker'
    args:
      - 'build'
      - '-t'
      - 'gcr.io/$PROJECT_ID/voice-media:$SHORT_SHA'
      - '-t'
      - 'gcr.io/$PROJECT_ID/voice-media:latest'
      - '-f'
      - 'services/media/Dockerfile'
      - '.'
    id: 'build-media'
  
  # Push images
  - name: 'gcr.io/cloud-builders/docker'
    args: ['push', 'gcr.io/$PROJECT_ID/voice-api:$SHORT_SHA']
    waitFor: ['build-api']
  
  - name: 'gcr.io/cloud-builders/docker'
    args: ['push', 'gcr.io/$PROJECT_ID/voice-signaling:$SHORT_SHA']
    waitFor: ['build-signaling']
  
  - name: 'gcr.io/cloud-builders/docker'
    args: ['push', 'gcr.io/$PROJECT_ID/voice-media:$SHORT_SHA']
    waitFor: ['build-media']
  
  # Deploy to GKE
  - name: 'gcr.io/cloud-builders/kubectl'
    args:
      - 'set'
      - 'image'
      - 'deployment/api-deployment'
      - 'api=gcr.io/$PROJECT_ID/voice-api:$SHORT_SHA'
      - '--namespace=voice-api'
    env:
      - 'CLOUDSDK_COMPUTE_REGION=us-east1'
      - 'CLOUDSDK_CONTAINER_CLUSTER=voice-platform-cluster'
  
  - name: 'gcr.io/cloud-builders/kubectl'
    args:
      - 'set'
      - 'image'
      - 'deployment/signaling-deployment'
      - 'signaling=gcr.io/$PROJECT_ID/voice-signaling:$SHORT_SHA'
      - '--namespace=voice-signaling'
    env:
      - 'CLOUDSDK_COMPUTE_REGION=us-east1'
      - 'CLOUDSDK_CONTAINER_CLUSTER=voice-platform-cluster'
  
  - name: 'gcr.io/cloud-builders/kubectl'
    args:
      - 'set'
      - 'image'
      - 'statefulset/media-server'
      - 'media=gcr.io/$PROJECT_ID/voice-media:$SHORT_SHA'
      - '--namespace=voice-media'
    env:
      - 'CLOUDSDK_COMPUTE_REGION=us-east1'
      - 'CLOUDSDK_CONTAINER_CLUSTER=voice-platform-cluster'

images:
  - 'gcr.io/$PROJECT_ID/voice-api:$SHORT_SHA'
  - 'gcr.io/$PROJECT_ID/voice-signaling:$SHORT_SHA'
  - 'gcr.io/$PROJECT_ID/voice-media:$SHORT_SHA'

options:
  machineType: 'N1_HIGHCPU_8'
  logging: CLOUD_LOGGING_ONLY
```

### 5.2 Setup Cloud Build Triggers

```bash
# Create trigger from GitHub
gcloud builds triggers create github \
  --repo-name=voice-platform \
  --repo-owner=YOUR_ORG \
  --branch-pattern="^main$" \
  --build-config=cloudbuild.yaml
```

---

## Phase 6: Backup & Disaster Recovery

### 6.1 Database Backups

```bash
# Automated backups (already configured in Terraform)
# Manual backup
gcloud sql backups create \
  --instance=voice-platform-db \
  --description="Manual backup before major update"

# List backups
gcloud sql backups list --instance=voice-platform-db

# Restore from backup
gcloud sql backups restore BACKUP_ID \
  --backup-instance=voice-platform-db \
  --backup-id=BACKUP_ID
```

### 6.2 GKE Cluster Backup (Velero)

```bash
# Install Velero
wget https://github.com/vmware-tanzu/velero/releases/download/v1.12.0/velero-v1.12.0-linux-amd64.tar.gz
tar -xvf velero-v1.12.0-linux-amd64.tar.gz
sudo mv velero-v1.12.0-linux-amd64/velero /usr/local/bin/

# Create GCS bucket for backups
gsutil mb gs://voice-platform-velero-backups/

# Install Velero in cluster
velero install \
  --provider gcp \
  --plugins velero/velero-plugin-for-gcp:v1.8.0 \
  --bucket voice-platform-velero-backups \
  --secret-file ./credentials-velero

# Create backup schedule
velero schedule create daily-backup \
  --schedule="0 2 * * *" \
  --include-namespaces voice-api,voice-signaling,voice-media
```

### 6.3 Disaster Recovery Plan

**RTO (Recovery Time Objective): 1 hour**
**RPO (Recovery Point Objective): 15 minutes**

Steps:
1. Database: Point-in-time recovery من Cloud SQL
2. Kubernetes: Restore من Velero backup
3. Secrets: Restore من Secret Manager
4. DNS: Update to new load balancer IP
5. Verification: Run smoke tests

---

## Phase 7: Security Hardening

### 7.1 Network Policies

```yaml
# network-policy-api.yaml
apiVersion: networking.k8s.io/v1
kind: NetworkPolicy
metadata:
  name: api-network-policy
  namespace: voice-api
spec:
  podSelector:
    matchLabels:
      app: api
  policyTypes:
  - Ingress
  - Egress
  ingress:
  - from:
    - namespaceSelector:
        matchLabels:
          name: voice-ingress
    ports:
    - protocol: TCP
      port: 8080
  egress:
  - to:
    - namespaceSelector:
        matchLabels:
          name: voice-signaling
  - to:
    - podSelector:
        matchLabels:
          app: postgres
    ports:
    - protocol: TCP
      port: 5432
  - to:
    - podSelector:
        matchLabels:
          app: redis
    ports:
    - protocol: TCP
      port: 6379
```

### 7.2 Pod Security Standards

```yaml
# pod-security-policy.yaml
apiVersion: policy/v1beta1
kind: PodSecurityPolicy
metadata:
  name: restricted
spec:
  privileged: false
  allowPrivilegeEscalation: false
  requiredDropCapabilities:
    - ALL
  volumes:
    - 'configMap'
    - 'emptyDir'
    - 'projected'
    - 'secret'
    - 'downwardAPI'
    - 'persistentVolumeClaim'
  runAsUser:
    rule: 'MustRunAsNonRoot'
  seLinux:
    rule: 'RunAsAny'
  fsGroup:
    rule: 'RunAsAny'
  readOnlyRootFilesystem: true
```

### 7.3 Cloud Armor (DDoS Protection)

```bash
# Create security policy
gcloud compute security-policies create voice-platform-policy \
  --description="Voice Platform Security Policy"

# Add rate limiting rule
gcloud compute security-policies rules create 100 \
  --security-policy=voice-platform-policy \
  --expression="true" \
  --action=rate-based-ban \
  --rate-limit-threshold-count=100 \
  --rate-limit-threshold-interval-sec=60 \
  --ban-duration-sec=600

# Attach to backend service
gcloud compute backend-services update voice-platform-backend \
  --security-policy=voice-platform-policy \
  --global
```

---

## Phase 8: Performance Optimization

### 8.1 Node Pool Autoscaling Configuration

```bash
# Update media node pool autoscaling
gcloud container node-pools update media-pool \
  --cluster=voice-platform-cluster \
  --enable-autoscaling \
  --min-nodes=40 \
  --max-nodes=100 \
  --location=us-east1

# Update API node pool autoscaling
gcloud container node-pools update api-pool \
  --cluster=voice-platform-cluster \
  --enable-autoscaling \
  --min-nodes=5 \
  --max-nodes=20 \
  --location=us-east1
```

### 8.2 Cluster Autoscaler Configuration

```yaml
# cluster-autoscaler-config.yaml
apiVersion: v1
kind: ConfigMap
metadata:
  name: cluster-autoscaler-priority-expander
  namespace: kube-system
data:
  priorities: |-
    10:
      - .*-media-pool.*
    5:
      - .*-api-pool.*
```

### 8.3 Vertical Pod Autoscaler

```bash
# Install VPA
kubectl apply -f https://raw.githubusercontent.com/kubernetes/autoscaler/master/vertical-pod-autoscaler/deploy/vpa-v1-crd.yaml
kubectl apply -f https://raw.githubusercontent.com/kubernetes/autoscaler/master/vertical-pod-autoscaler/deploy/vpa-rbac.yaml
kubectl apply -f https://raw.githubusercontent.com/kubernetes/autoscaler/master/vertical-pod-autoscaler/deploy/vpa-deployment.yaml
```

---

## Phase 9: Cost Optimization

### 9.1 Committed Use Discounts

```bash
# Purchase 1-year commitment للـ compute
gcloud compute commitments create voice-platform-commitment \
  --resources=vcpu=320,memory=1280 \
  --plan=twelve-month \
  --region=us-east1
```

### 9.2 Preemptible VMs (للـ non-critical workloads)

```hcl
# للـ development/staging environments
resource "google_container_node_pool" "dev_pool" {
  # ... other config
  
  node_config {
    preemptible  = true
    machine_type = "n2-standard-4"
  }
}
```

### 9.3 Network Egress Optimization

- استخدام Cloud CDN للـ static content
- Compression للـ audio streams
- Regional routing بدلاً من global

---

## 📊 Monitoring Dashboards

### Key Metrics to Track:

1. **Infrastructure:**
   - Node CPU/Memory utilization
   - Network bandwidth
   - Disk I/O

2. **Application:**
   - Active rooms count
   - Connected users per room
   - Audio latency (p50, p95, p99)
   - Packet loss rate
   - WebRTC connection success rate

3. **Database:**
   - Query performance
   - Connection pool usage
   - Lock waits

4. **Cost:**
   - Daily spend by service
   - Network egress costs
   - Compute costs per node pool

---

## 🚨 Incident Response Plan

### Severity Levels:

**P0 (Critical):**
- Complete service outage
- Response time: < 15 minutes
- Full team mobilization

**P1 (High):**
- Major feature broken
- Response time: < 1 hour

**P2 (Medium):**
- Minor feature degradation
- Response time: < 4 hours

**P3 (Low):**
- Cosmetic issues
- Response time: Next business day

### On-Call Rotation:
- Primary: DevOps Engineer 1
- Secondary: DevOps Engineer 2
- Escalation: Senior DevOps Lead

---

## 📋 Deployment Checklist

### Pre-Deployment:
- [ ] All tests passing in CI
- [ ] Database migrations reviewed
- [ ] Rollback plan documented
- [ ] Monitoring alerts configured
- [ ] Load testing completed
- [ ] Security scan passed
- [ ] Changelog updated

### During Deployment:
- [ ] Blue-green deployment or canary release
- [ ] Monitor error rates
- [ ] Check latency metrics
- [ ] Verify new features

### Post-Deployment:
- [ ] Smoke tests passed
- [ ] Performance metrics normal
- [ ] No critical alerts
- [ ] Update documentation

---

## 🔧 Troubleshooting Common Issues

### Issue 1: High Pod Evictions
```bash
# Check node pressure
kubectl describe node NODE_NAME | grep -A 5 Conditions

# Check pod resource usage
kubectl top pods -n voice-media --sort-by=memory

# Solution: Increase node resources or adjust limits
```

### Issue 2: Database Connection Pool Exhausted
```bash
# Check active connections
kubectl exec -it postgres-pod -- psql -U postgres -c "SELECT count(*) FROM pg_stat_activity;"

# Solution: Increase max_connections or optimize queries
```

### Issue 3: High Network Latency
```bash
# Check network policies
kubectl get networkpolicies -A

# Test connectivity
kubectl run -it --rm debug --image=nicolaka/netshoot --restart=Never -- bash
```

---

## 📚 Documentation & Runbooks

### Required Documentation:
1. **Architecture Diagrams** (draw.io/Lucidchart)
2. **Runbooks** لكل service
3. **API Documentation** (Swagger/OpenAPI)
4. **Terraform Documentation**
5. **Disaster Recovery Procedures**
6. **Onboarding Guide** للفريق الجديد

---

## 🎓 Training & Knowledge Transfer

### DevOps Team Skills Required:
- GCP Services (GKE, Cloud SQL, etc.)
- Kubernetes administration
- Terraform/IaC
- Monitoring (Prometheus/Grafana)
- CI/CD (Cloud Build)
- Networking fundamentals
- Security best practices

### Recommended Certifications:
- Google Cloud Professional Cloud Architect
- Certified Kubernetes Administrator (CKA)
- Certified Kubernetes Security Specialist (CKS)

---

## ✅ Success Criteria

### Performance:
- [ ] Audio latency < 150ms (p95)
- [ ] 99.9% uptime
- [ ] < 1% packet loss
- [ ] Support 2000+ concurrent rooms

### Scalability:
- [ ] Auto-scale from 40 to 100 media servers
- [ ] Handle traffic spikes
- [ ] Zero-downtime deployments

### Security:
- [ ] All traffic encrypted (TLS)
- [ ] Network policies enforced
- [ ] Secrets managed properly
- [ ] Regular security audits

### Cost:
- [ ] Stay within budget
- [ ] Optimize resource usage
- [ ] Use committed use discounts

---

## 📞 Support & Escalation

### Contact Points:
- **DevOps Lead:** devops-lead@company.com
- **Backend Lead:** backend-lead@company.com
- **GCP Support:** Priority Support ticket
- **On-Call:** PagerDuty integration

---

**Last Updated:** نوفمبر 2025
**Version:** 1.0
**Maintained by:** DevOps Team
