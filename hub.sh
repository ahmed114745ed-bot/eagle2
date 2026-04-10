#!/bin/bash
# ======================================================
# Script Name : hub.sh
# Description : Safe and clear git pull + permission fix
# Author      : Abdallah Abdelfattah
# ======================================================

# ---- CONFIGURATION ----
LOG_FILE="/var/log/hub_script.log"
APP_DIR="$(pwd)"  # You can change to your app path if needed
STORAGE_DIR="storage"

# ---- COLORS ----
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ---- FUNCTIONS ----
log() {
  echo -e "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

ok() {
  echo -e "${GREEN}✅ SUCCESS:${NC} $1"
  log "SUCCESS: $1"
}

warn() {
  echo -e "${YELLOW}⚠️  WARNING:${NC} $1"
  log "WARNING: $1"
}

error() {
  echo -e "${RED}❌ ERROR:${NC} $1"
  log "ERROR: $1"
  exit 1
}

info() {
  echo -e "${BLUE}ℹ️ INFO:${NC} $1"
  log "INFO: $1"
}

# ---- MAIN ----
info "Starting hub update script..."
cd "$APP_DIR" || error "Failed to change directory to $APP_DIR"

# Ensure .git directory exists
if [ ! -d ".git" ]; then
  error "No .git directory found. Are you in a Git repository?"
fi

info "Current Git Branch:"
sudo git branch || error "Failed to list branches"

info "Git Remotes:"
sudo git remote -v || error "Failed to list git remotes"

info "Pulling latest changes..."
if sudo git pull; then
  ok "Git pull completed successfully."
else
  error "Git pull failed. Please check your network or branch conflicts."
fi

info "Fixing permissions for ${STORAGE_DIR}/ ..."
if [ -d "$STORAGE_DIR" ]; then
  sudo chmod -R 777 "$STORAGE_DIR" || error "Failed to set permissions on storage/"
  sudo chown -R www-data:www-data "$STORAGE_DIR" || error "Failed to change ownership on storage/"
  ok "Permissions fixed for ${STORAGE_DIR}/."
else
  warn "Storage directory not found at ${STORAGE_DIR}/. Skipping permission fix."
fi

ok "All tasks completed successfully 🎉"
return 0 2>/dev/null || true

