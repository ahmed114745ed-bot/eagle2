<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow p-6 w-[820px] max-h-[80vh] overflow-y-auto">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold">Assets Library</h3>
        <button @click="$emit('close')" class="px-2 py-1 bg-gray-200 rounded">Close</button>
      </div>

      

      <div class="grid grid-cols-3 gap-4">
        <div v-for="asset in filteredAssets" :key="asset.id" class="border rounded p-2 flex flex-col">
        
       
       <div class="flex-1 mb-2">
        <div v-if="asset.type === 'text'" class="w-full h-32 bg-gray-100 p-2 overflow-auto rounded text-sm">
          {{ asset.text  }}
        </div>

<div v-else-if="asset.type === 'file'" class="flex-1 mb-2">
  <img
    v-if="asset.asset_type === 'image' && imageSrc(asset)"
    :src="imageSrc(asset)"
    class="w-full h-32 object-cover rounded"
  />
  <div
    v-else
    class="w-full h-32 bg-gray-100 flex items-center justify-center rounded text-sm text-gray-700"
  >
    {{ asset.asset_type }}
  </div>
</div>


</div>


          <div class="text-sm font-medium">{{ asset.asset_label || asset.asset_key || asset.original_filename }}</div>
          <div class="mt-2 flex items-center gap-2">
            <button @click="select(asset)" class="px-2 py-1 bg-green-600 text-white rounded">Select</button>
            <a v-if="asset.default_url" :href="asset.default_url" target="_blank" class="px-2 py-1 bg-gray-200 rounded text-sm">View</a>
          </div>
        </div>
      </div>

      <div class="mt-4 flex justify-between items-center">
        <div>
          <button @click="prevPage" :disabled="!assets.prev_page_url" class="px-2 py-1 bg-gray-200 rounded mr-2">Prev</button>
          <button @click="nextPage" :disabled="!assets.next_page_url" class="px-2 py-1 bg-gray-200 rounded">Next</button>
        </div>
        <div class="text-sm text-gray-500">Showing {{ assets.from || 0 }} - {{ assets.to || 0 }} of {{ assets.total || 0 }}</div>
      </div>

      <hr class="my-4" />

     
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { ref, onMounted, watch, computed } from 'vue';

export default {
  name: 'AssetLibraryModal',
  props: {
    initialType: {
      type: String,
      default: ''
    }
  },
  emits: ['close', 'select'],
  setup(props, { emit }) {

    const search = ref('');
    const assets = ref({ data: [], total: 0 });
    const pageUrl = ref('/api/dashboard/library/assets');

    const upload = ref({ asset_type: 'image', asset_label: '', file: null });
    const uploading = ref(false);

    const filteredAssets = computed(() => {
      const data = assets.value.data || [];
      if (!props.initialType) return data;
      if (props.initialType === 'text') return data.filter((a) => a.type === 'text');
      const filtered = data.filter((a) => a.asset_type === props.initialType);
      console.log('[Library] filtered', { initialType: props.initialType, before: data.length, after: filtered.length });
      return filtered;
    });

    // دالة تحميل الأصول حسب النوع
    const loadAssets = async (url) => {
      try {
        const params = { search: search.value };
        // نرسل الفلتر للباك فقط لو كان ملف، لأن الفلتر يعتمد على asset_type في الـ API
        if (props.initialType && props.initialType !== 'text') {
          params.type = props.initialType;
        }
        console.log('[Library] loading assets', { url: url || pageUrl.value, params });
        const res = await axios.get(url || pageUrl.value, { params });
        assets.value = res.data;
        console.log('[Library] assets loaded', { total: res.data?.total, count: res.data?.data?.length });
      } catch (err) {
        console.error('Failed to load library assets', err);
      }
    };

    // إعادة تحميل عند تغيير النوع
    watch(() => props.initialType, () => {
      loadAssets();
    });

    onMounted(() => loadAssets());

    const select = (asset) => {
      emit('select', asset);
      emit('close');
    };

    const nextPage = () => {
      if (assets.value.next_page_url) loadAssets(assets.value.next_page_url);
    };
    const prevPage = () => {
      if (assets.value.prev_page_url) loadAssets(assets.value.prev_page_url);
    };

    const onUploadFileChange = (e) => {
      upload.value.file = e.target.files?.[0] || null;
    };

    const imageSrc = (asset) => {
      if (asset.file_path) return `/storage/${asset.file_path}`;
      if (asset.default_url) return asset.default_url;
      return null;
    };

    const uploadToLibrary = async () => {
      if (!upload.value.file) {
        alert('Please choose a file');
        return;
      }
      uploading.value = true;
      try {
        const fd = new FormData();
        fd.append('file', upload.value.file);
        fd.append('asset_type', upload.value.asset_type);
        fd.append('asset_label', upload.value.asset_label);
        await axios.post('/api/dashboard/library/assets', fd, { headers: { 'Content-Type': 'multipart/form-data' } });
        await loadAssets(); // إعادة تحميل بعد الرفع
        upload.value = { asset_type: 'image', asset_label: '', file: null };
        alert('Uploaded to library');
      } catch (err) {
        console.error('Upload failed', err);
        alert('Upload failed');
      } finally {
        uploading.value = false;
      }
    };

    return { search, assets, filteredAssets, loadAssets, select, nextPage, prevPage, upload, onUploadFileChange, uploadToLibrary, uploading, imageSrc };
  },
};
</script>
