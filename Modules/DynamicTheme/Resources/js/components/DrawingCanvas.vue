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

<style scoped lang="postcss">
.drawing-canvas-container {
  @apply bg-gray-50 rounded-lg shadow-lg p-6;
}

.drawing-header {
  @apply mb-6 bg-white rounded-lg p-4 shadow;
}

.drawing-header h2 {
  @apply text-2xl font-bold text-gray-800 mb-4;
}

.tool-options {
  @apply flex flex-wrap gap-4 items-center;
}

.tool-section {
  @apply flex items-center gap-2;
}

.tool-section label {
  @apply font-semibold text-gray-700 text-sm;
}

.tool-select,
.color-picker {
  @apply border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-500;
}

.size-slider,
.opacity-slider {
  @apply w-24;
}

.action-buttons {
  @apply flex flex-wrap gap-2;
}

.btn {
  @apply px-3 py-2 rounded font-semibold text-sm cursor-pointer transition-colors;
}

.btn-primary {
  @apply bg-blue-600 text-white hover:bg-blue-700;
}

.btn-success {
  @apply bg-green-600 text-white hover:bg-green-700;
}

.btn-danger {
  @apply bg-red-600 text-white hover:bg-red-700;
}

.btn-warning {
  @apply bg-yellow-600 text-white hover:bg-yellow-700;
}

.btn-info {
  @apply bg-cyan-600 text-white hover:bg-cyan-700;
}

.canvas-wrapper {
  @apply mb-6 bg-white rounded-lg shadow-md overflow-hidden;
}

.drawing-canvas {
  @apply border-2 border-dashed border-gray-300 cursor-crosshair w-full block;
  display: block;
  max-width: 100%;
}

.info-panel {
  @apply bg-white rounded-lg shadow p-4 grid grid-cols-3 gap-4;
}

.info-item {
  @apply text-sm text-gray-700;
}

.info-item strong {
  @apply text-gray-900 font-semibold;
}
</style>
