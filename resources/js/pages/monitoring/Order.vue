<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps<{
  pembayarans: {
    id_pembayaran: string;
    order_id: string;
    created_at: string;
    bayar: number;
    kembalian: number;
    status: { nama: string };
    kategori: { nama: string };
    order: {
        id_order: string;
        nama_pelanggan: string;
        tgl_deadline: string;
    };
  }[];
  desainDatas: {
    id: number;
    order_id: string;
    tgl_terima: string | null;
    status: { nama: string };
    order: {
      id_order: string;
      nama_pelanggan: string;
      tgl_deadline: string;
    };
  }[];
  orders: {
    id_order: number;
    tgl_deadline: string;
    nama_pelanggan: string;
    status: { nama: string };
  }[];
}>();

// Dummy data lokal langsung
const orders = {
  paymentQueue: props.pembayarans,
  designQueue: props.desainDatas,
  productionQueue: props.orders,
};

// Refs untuk queue container
const paymentQueueRef = ref<HTMLElement | null>(null);
const designQueueRef = ref<HTMLElement | null>(null);
const productionQueueRef = ref<HTMLElement | null>(null);

let scrollInterval: ReturnType<typeof setInterval> | null = null;
let refreshInterval: ReturnType<typeof setInterval> | null = null;

const startAutoScroll = () => {
  scrollInterval = setInterval(() => {
    [paymentQueueRef.value, designQueueRef.value, productionQueueRef.value].forEach(container => {
      if (container) {
        container.scrollTop += 1;
        if (container.scrollTop + container.clientHeight >= container.scrollHeight) {
          container.scrollTop = 0;
        }
      }
    });
  }, 100);
};

const startAutoRefresh = () => {
  refreshInterval = setInterval(() => {
    router.reload({
      only: ['pembayarans', 'desainDatas', 'orders'],
    });
  }, 5000);
};

onMounted(() => {
    document.documentElement.requestFullscreen?.();
    startAutoScroll();
    startAutoRefresh();
});

onBeforeUnmount(() => {
    if (scrollInterval) clearInterval(scrollInterval);
    if (refreshInterval) clearInterval(refreshInterval);
});
</script>

<template>
  <Head title="Deadline" />

    <div class="h-screen p-6 grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
        <!-- Antrian Pembayaran -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden h-[calc(100vh-160px)]">
            <div class="bg-red-500 text-white p-4 font-bold flex justify-between">
                <span><i class="fas fa-money-bill-wave mr-2"></i>Antrian Pembayaran</span>
                <span class="bg-white text-red-500 px-3 py-1 rounded-full text-xs">{{ pembayarans.length }} Order</span>
            </div>
            <div ref="paymentQueueRef" class="queue-container h-[calc(100%_-_54px)] overflow-y-auto p-3">
                <div v-for="item in pembayarans" :key="item.id_pembayaran" class="order-card bg-white border p-4 mb-3 rounded-lg">
                    <div class="flex justify-between">
                        <span class="font-bold text-indigo-600">{{ item.order_id }}</span>
                        <span class="status-badge bg-yellow-100 text-yellow-800">
                            {{ item.status.nama }}
                        </span>
                    </div>
                    <div class="mt-2 font-semibold">{{ item.order?.nama_pelanggan ?? '-' }}</div>
                    <div class="text-sm text-gray-600 flex justify-between mt-2">
                        <span><i class="far fa-clock mr-1"></i>{{ item.order?.tgl_deadline ?? '-' }}</span>
                        <span class="text-xs text-red-500">
                            {{ item.kategori.nama }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Antrian Desain -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden h-[calc(100vh-160px)]">
            <div class="bg-amber-500 text-white p-4 font-bold flex justify-between">
                <span><i class="fas fa-pencil-ruler mr-2"></i>Antrian Desain</span>
                <span class="bg-white text-amber-500 px-3 py-1 rounded-full text-xs">
                {{ desainDatas.length }} Order
                </span>
            </div>
            <div ref="designQueueRef" class="queue-container h-[calc(100%_-_54px)] overflow-y-auto p-3">
                <div v-for="item in desainDatas" :key="item.id" class="order-card bg-white border p-4 mb-3 rounded-lg">
                    <div class="flex justify-between">
                        <span class="font-bold text-indigo-600">{{ item.order?.id_order }}</span>
                        <span class="status-badge bg-yellow-100 text-yellow-800">
                        {{ item.status?.nama }}
                        </span>
                    </div>
                    <div class="mt-2 font-semibold">{{ item.order?.nama_pelanggan ?? '-' }}</div>
                    <div class="text-sm text-gray-600 flex justify-between mt-2">
                        <span><i class="far fa-clock mr-1"></i>{{ item.order?.tgl_deadline ?? '-' }}</span>
                        <span class="text-xs text-amber-500">
                            Proses
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Antrian Produksi -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden h-[calc(100vh-160px)]">
          <div class="bg-emerald-500 text-white p-4 font-bold flex justify-between">
              <span><i class="fas fa-tshirt mr-2"></i>Antrian Produksi</span>
              <span class="bg-white text-emerald-500 px-3 py-1 rounded-full text-xs">{{ orders.productionQueue.length }} Order</span>
          </div>
          <div ref="productionQueueRef" class="queue-container h-[calc(100%_-_54px)] overflow-y-auto p-3">
              <div
              v-for="item in orders.productionQueue"
              :key="item.id_order"
              class="order-card bg-white border p-4 mb-3 rounded-lg"
              >
              <div class="flex justify-between">
                  <span class="font-bold text-indigo-600">{{ item.id_order }}</span>
                  <span class="status-badge" :class="{
                  'bg-yellow-100 text-yellow-800': item.status.nama === 'Cetak & Print',
                  'bg-blue-100 text-blue-800': item.status.nama === 'Press Kain',
                  'bg-purple-100 text-purple-800': item.status.nama === 'Cutting Kain',
                  'bg-pink-100 text-pink-800': item.status.nama === 'Jahit',
                  'bg-red-100 text-red-800': item.status.nama === 'Sablon & Press Kecil',
                  'bg-indigo-100 text-indigo-800': item.status.nama === 'QC',
                  'bg-green-100 text-green-800': item.status.nama === 'Packaging'
                  }">
                  {{ item.status.nama }}
                  </span>
              </div>
              <div class="mt-2 font-semibold">{{ item.nama_pelanggan }}</div>
              <div class="text-sm text-gray-600 flex justify-between mt-2">
                  <span><i class="far fa-clock mr-1"></i>{{ item.tgl_deadline }}</span>
                  <span class="text-xs text-green-500"><i class="fas fa-play mr-1"></i>Proses</span>
              </div>
              </div>
          </div>
        </div>
    </div>
</template>

<style scoped>
.order-card {
  transition: all 0.3s ease;
}
.order-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}
.status-badge {
  border-radius: 9999px;
  padding: 0.25rem 0.75rem;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: uppercase;
}
.queue-container::-webkit-scrollbar {
  width: 4px;
}
.queue-container::-webkit-scrollbar-thumb {
  background-color: #ccc;
  border-radius: 5px;
}
.queue-container {
  scroll-behavior: smooth;
}
</style>
