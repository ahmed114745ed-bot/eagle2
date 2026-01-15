<template>
  <div class="drawing-canvas-container">
    <!-- Header -->
    <div class="drawing-header">
      <h2>🎨 أداة الرسم - Drawing Tool</h2>
      <div class="tool-options">
        <div class="tool-section">
          <label>أداة:</label>
          <select v-model="currentTool" class="tool-select">
            <option value="pen">✏️ قلم (Pen)</option>
            <option value="line">📏 خط (Line)</option>
            <option value="rectangle">📦 مستطيل (Rectangle)</option>
            <option value="circle">⭕ دائرة (Circle)</option>
            <option value="eraser">🧹 ممحاة (Eraser)</option>
          </select>
        </div>

        <div class="tool-section">
          <label>حجم الفرشاة:</label>
          <input 
            v-model.number="brushSize" 
            type="range" 
            min="1" 
            max="50" 
            class="size-slider"
          />
          <span>{{ brushSize }}px</span>
        </div>

        <div class="tool-section">
          <label>اللون:</label>
          <input 
            v-model="brushColor" 
            type="color" 
            class="color-picker"
          />
        </div>

        <div class="tool-section">
          <label>العتامة:</label>
          <input 
            v-model.number="brushOpacity" 
            type="range" 
            min="0" 
            max="1" 
            step="0.1" 
            class="opacity-slider"
          />
          <span>{{ Math.round(brushOpacity * 100) }}%</span>
        </div>

        <div class="action-buttons">
          <button @click="clearCanvas" class="btn btn-danger">🗑️ مسح</button>
          <button @click="undoDrawing" class="btn btn-warning">↶ تراجع</button>
          <button @click="redoDrawing" class="btn btn-info">↷ إعادة</button>
          <button @click="downloadDrawing" class="btn btn-success">⬇️ حفظ صورة</button>
          <button @click="saveDrawingData" class="btn btn-primary">💾 حفظ في قاعدة البيانات</button>
        </div>
      </div>
    </div>

    <!-- Canvas -->
    <div class="canvas-wrapper">
      <canvas
        ref="canvas"
        @mousedown="startDrawing"
        @mousemove="draw"
        @mouseup="stopDrawing"
        @mouseout="stopDrawing"
        @touchstart="startDrawingTouch"
        @touchmove="drawTouch"
        @touchend="stopDrawing"
        class="drawing-canvas"
      ></canvas>
    </div>

    <!-- Info Panel -->
    <div class="info-panel">
      <div class="info-item">
        <strong>حجم اللوحة:</strong> {{ canvasWidth }} × {{ canvasHeight }}px
      </div>
      <div class="info-item">
        <strong>الأداة الحالية:</strong> {{ toolLabels[currentTool] }}
      </div>
      <div class="info-item">
        <strong>عدد الخطوات:</strong> {{ drawingHistory.length }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DrawingCanvas',
  props: {
    childCustomizerId: {
      type: Number,
      required: true
    },
    initialDrawingData: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      canvas: null,
      ctx: null,
      canvasWidth: 800,
      canvasHeight: 600,
      
      // Drawing tools
      currentTool: 'pen',
      brushSize: 3,
      brushColor: '#000000',
      brushOpacity: 1,
      
      // Drawing state
      isDrawing: false,
      startX: 0,
      startY: 0,
      
      // History
      drawingHistory: [],
      historyStep: -1,
      
      // Tool labels
      toolLabels: {
        pen: '✏️ قلم',
        line: '📏 خط',
        rectangle: '📦 مستطيل',
        circle: '⭕ دائرة',
        eraser: '🧹 ممحاة'
      }
    };
  },
  
  mounted() {
    this.initializeCanvas();
    if (this.initialDrawingData) {
      this.loadDrawingData(this.initialDrawingData);
    }
  },
  
  methods: {
    initializeCanvas() {
      this.canvas = this.$refs.canvas;
      this.ctx = this.canvas.getContext('2d');
      
      // Set canvas size
      this.canvas.width = this.canvasWidth;
      this.canvas.height = this.canvasHeight;
      
      // Fill with white background
      this.ctx.fillStyle = '#ffffff';
      this.ctx.fillRect(0, 0, this.canvasWidth, this.canvasHeight);
      
      // Save initial state
      this.saveHistory();
    },
    
    startDrawing(e) {
      const rect = this.canvas.getBoundingClientRect();
      this.startX = e.clientX - rect.left;
      this.startY = e.clientY - rect.top;
      this.isDrawing = true;
      
      // For pen tool, start drawing immediately
      if (this.currentTool === 'pen') {
        this.ctx.beginPath();
        this.ctx.moveTo(this.startX, this.startY);
      }
    },
    
    startDrawingTouch(e) {
      const touch = e.touches[0];
      const rect = this.canvas.getBoundingClientRect();
      this.startX = touch.clientX - rect.left;
      this.startY = touch.clientY - rect.top;
      this.isDrawing = true;
      
      if (this.currentTool === 'pen') {
        this.ctx.beginPath();
        this.ctx.moveTo(this.startX, this.startY);
      }
    },
    
    draw(e) {
      if (!this.isDrawing) return;
      
      const rect = this.canvas.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      
      this.drawOnCanvas(x, y);
    },
    
    drawTouch(e) {
      if (!this.isDrawing) return;
      
      const touch = e.touches[0];
      const rect = this.canvas.getBoundingClientRect();
      const x = touch.clientX - rect.left;
      const y = touch.clientY - rect.top;
      
      this.drawOnCanvas(x, y);
    },
    
    drawOnCanvas(x, y) {
      this.setupContextStyle();
      
      switch (this.currentTool) {
        case 'pen':
          this.ctx.lineTo(x, y);
          this.ctx.stroke();
          break;
          
        case 'line':
          this.redrawFromHistory();
          this.ctx.beginPath();
          this.ctx.moveTo(this.startX, this.startY);
          this.ctx.lineTo(x, y);
          this.ctx.stroke();
          break;
          
        case 'rectangle':
          this.redrawFromHistory();
          const width = x - this.startX;
          const height = y - this.startY;
          this.ctx.strokeRect(this.startX, this.startY, width, height);
          break;
          
        case 'circle':
          this.redrawFromHistory();
          const radius = Math.sqrt(Math.pow(x - this.startX, 2) + Math.pow(y - this.startY, 2));
          this.ctx.beginPath();
          this.ctx.arc(this.startX, this.startY, radius, 0, 2 * Math.PI);
          this.ctx.stroke();
          break;
          
        case 'eraser':
          this.ctx.clearRect(x - this.brushSize / 2, y - this.brushSize / 2, this.brushSize, this.brushSize);
          break;
      }
    },
    
    setupContextStyle() {
      this.ctx.lineWidth = this.brushSize;
      this.ctx.lineCap = 'round';
      this.ctx.lineJoin = 'round';
      
      if (this.currentTool === 'eraser') {
        this.ctx.globalCompositeOperation = 'destination-out';
        this.ctx.strokeStyle = 'rgba(0,0,0,1)';
      } else {
        this.ctx.globalCompositeOperation = 'source-over';
        const color = this.hexToRgb(this.brushColor);
        this.ctx.strokeStyle = `rgba(${color.r},${color.g},${color.b},${this.brushOpacity})`;
        this.ctx.fillStyle = `rgba(${color.r},${color.g},${color.b},${this.brushOpacity})`;
      }
    },
    
    hexToRgb(hex) {
      const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
      return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
      } : { r: 0, g: 0, b: 0 };
    },
    
    stopDrawing() {
      if (this.isDrawing) {
        this.isDrawing = false;
        if (this.currentTool === 'pen') {
          this.ctx.closePath();
        }
        this.saveHistory();
      }
    },
    
    saveHistory() {
      // Remove any redo history
      this.drawingHistory = this.drawingHistory.slice(0, this.historyStep + 1);
      
      // Save current canvas state
      this.drawingHistory.push(this.canvas.toDataURL());
      this.historyStep = this.drawingHistory.length - 1;
    },
    
    redrawFromHistory() {
      if (this.historyStep >= 0 && this.drawingHistory[this.historyStep]) {
        const img = new Image();
        img.src = this.drawingHistory[this.historyStep];
        img.onload = () => {
          this.ctx.clearRect(0, 0, this.canvasWidth, this.canvasHeight);
          this.ctx.drawImage(img, 0, 0);
        };
      }
    },
    
    undoDrawing() {
      if (this.historyStep > 0) {
        this.historyStep--;
        const img = new Image();
        img.src = this.drawingHistory[this.historyStep];
        img.onload = () => {
          this.ctx.clearRect(0, 0, this.canvasWidth, this.canvasHeight);
          this.ctx.drawImage(img, 0, 0);
        };
      }
    },
    
    redoDrawing() {
      if (this.historyStep < this.drawingHistory.length - 1) {
        this.historyStep++;
        const img = new Image();
        img.src = this.drawingHistory[this.historyStep];
        img.onload = () => {
          this.ctx.clearRect(0, 0, this.canvasWidth, this.canvasHeight);
          this.ctx.drawImage(img, 0, 0);
        };
      }
    },
    
    clearCanvas() {
      this.ctx.fillStyle = '#ffffff';
      this.ctx.fillRect(0, 0, this.canvasWidth, this.canvasHeight);
      this.saveHistory();
    },
    
    downloadDrawing() {
      const link = document.createElement('a');
      link.href = this.canvas.toDataURL('image/png');
      link.download = `child-drawing-${this.childCustomizerId}-${Date.now()}.png`;
      link.click();
    },
    
    async saveDrawingData() {
      const drawingData = this.canvas.toDataURL('image/png');
      const drawingJson = this.getDrawingMetadata();
      
      this.$emit('save-drawing', {
        drawingData,
        drawingJson,
        childCustomizerId: this.childCustomizerId
      });
    },
    
    getDrawingMetadata() {
      return {
        width: this.canvasWidth,
        height: this.canvasHeight,
        savedAt: new Date().toISOString(),
        steps: this.historyStep + 1
      };
    },
    
    loadDrawingData(drawingData) {
      const img = new Image();
      img.src = drawingData;
      img.onload = () => {
        this.ctx.clearRect(0, 0, this.canvasWidth, this.canvasHeight);
        this.ctx.drawImage(img, 0, 0);
        this.saveHistory();
      };
    }
  }
};
</script>

<style scoped>
.drawing-canvas-container {
  background-color: #f9fafb;
  border-radius: 0.5rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  padding: 1.5rem;
}

.drawing-header {
  margin-bottom: 1.5rem;
  background-color: white;
  border-radius: 0.5rem;
  padding: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.drawing-header h2 {
  font-size: 1.5rem;
  font-weight: bold;
  color: #1f2937;
  margin-bottom: 1rem;
}

.tool-options {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  align-items: center;
}

.tool-section {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.tool-section label {
  font-weight: 600;
  color: #374151;
  font-size: 0.875rem;
}

.tool-select,
.color-picker {
  border: 1px solid #d1d5db;
  border-radius: 0.25rem;
  padding: 0.5rem;
  focus: ring 2px #3b82f6;
}

.size-slider,
.opacity-slider {
  width: 6rem;
}

.action-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.btn {
  padding: 0.75rem;
  border-radius: 0.25rem;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.btn-primary {
  background-color: #2563eb;
  color: white;
}

.btn-primary:hover {
  background-color: #1d4ed8;
}

.btn-success {
  background-color: #16a34a;
  color: white;
}

.btn-success:hover {
  background-color: #15803d;
}

.btn-danger {
  background-color: #dc2626;
  color: white;
}

.btn-danger:hover {
  background-color: #b91c1c;
}

.btn-warning {
  background-color: #ca8a04;
  color: white;
}

.btn-warning:hover {
  background-color: #a16207;
}

.btn-info {
  background-color: #06b6d4;
  color: white;
}

.btn-info:hover {
  background-color: #0891b2;
}

.canvas-wrapper {
  margin-bottom: 1.5rem;
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.drawing-canvas {
  border: 2px dashed #d1d5db;
  cursor: crosshair;
  width: 100%;
  display: block;
  max-width: 100%;
}

.info-panel {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 1rem;
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 1rem;
}

.info-item {
  font-size: 0.875rem;
  color: #374151;
}

.info-item strong {
  color: #111827;
  font-weight: 600;
}
</style>
