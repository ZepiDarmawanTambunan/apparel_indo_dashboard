<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { ref, computed } from 'vue';
import { DataTable, Column } from 'primevue';
import { toast } from 'vue3-toastify';
import { useConfirm, ConfirmDialog } from 'primevue';

interface Kategori {
  id: number;
  nama: string;
}

interface LaporanKerusakan {
    id: number;
    order_id: string;
    divisi_pelapor: string;
    status: Kategori | null;
    status_checking: Kategori | null;
    pelapor_nama: string;
    jumlah_rusak: number;
    divisi_bertanggung_jawab: string;
    keterangan: string | null;
    keterangan_checking: string | null;
    is_human_error: boolean;
    foto_kerusakan: string;
}

const props = defineProps<{
  order_id: string;
  laporan_kerusakans: LaporanKerusakan[];
  breadcrumbs: { title: string; href?: string }[];
}>();

const breadcrumbs = props.breadcrumbs;
const laporan_kerusakans = props.laporan_kerusakans;
const search = ref('');

const filteredLaporanKerusakans = computed(() => {
  if (!search.value) return laporan_kerusakans;
  return laporan_kerusakans.filter(l =>
    l.order_id.toLowerCase().includes(search.value.toLowerCase()) ||
    l.divisi_pelapor.toLowerCase().includes(search.value.toLowerCase())
  );
});
</script>

<template>
  <Head :title="`Pembayaran Order ${order_id}`" />

  <AppLayout>
    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl p-6 mt-5 shadow hover:shadow-lg transition">
            <Breadcrumbs :breadcrumbs="breadcrumbs" />

            <DataTable
                style="margin-top: 30px;"
                :value="laporan_kerusakans"
                dataKey="id"
                responsiveLayout="scroll"
                >
                <template #empty>
                    <div class="flex items-center justify-center py-10 text-gray-400">
                        Laporan kerusakan masih kosong.
                    </div>
                </template>
                <Column field="created_at" header="Tgl" />
                <Column field="divisi_pelapor" header="Divisi Pelapor" />
                <Column field="pelapor_nama" header="Pelapor" />
                <Column field="keterangan" header="Keterangan" />
                <Column field="status.nama" header="Status" />
                <Column header="Aksi">
                    <template #body="slotProps">
                            <div class="flex gap-2">
                            <a
                                :href="slotProps.data.foto_kerusakan"
                                target="_blank"
                                rel="noopener"
                                class="px-3 py-2 text-sm font-medium rounded bg-blue-500 text-white hover:bg-blue-600"
                            >
                                Lihat
                            </a>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
    <ConfirmDialog />
  </AppLayout>
</template>
