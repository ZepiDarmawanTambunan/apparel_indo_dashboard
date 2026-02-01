<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { toast } from 'vue3-toastify';

interface Kategori {
  id: number;
  nama: string;
}

interface OrderTambahan {
  id: number;
  nama: string;
  harga: number;
}

interface OrderDetail {
  id: number;
  nama: string;
  qty: number;
  harga: number;
  order_tambahan: OrderTambahan[];
}

interface Order {
  id_order: string;
  nama_pelanggan: string;
  created_at: string;
  keterangan: string;
  lainnya: number;
  diskon: number;
  total: number;
  user_nama: string;
  order_detail: OrderDetail[];
}

interface Pembayaran {
  bayar: number;
  kembalian: number;
  bukti_pembayaran: string;
}

interface Invoice {
  id_invoice: string;
  kategori: Kategori;
  status: Kategori;
  order: Order;
  pembayaran: Pembayaran;
  keterangan: string;
}

const props = defineProps<{ invoice: Invoice }>();

const invoice = props.invoice;
const order = invoice.order;

const subTotal = computed(() =>
  order.order_detail.reduce((acc, item) => acc + item.harga * item.qty, 0)
);

const form = useForm({
  id_invoice: invoice.id_invoice,
  keterangan: invoice.keterangan,
  status: invoice.status.nama,
});

function submit(status: 'batal' | 'selesai') {
  form.status = status;
  form.post(route('invoice.konfirmasi', form.id_invoice), {
    onSuccess: () => console.log(`Invoice ${status}`),
    onError: (errors) => {
      toast.error(errors.error, {
        autoClose: 5000,
        position: 'top-right',
      });
    },
  });
}
</script>

<template>
  <Head title="Invoice" />

  <AppLayout>
    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- ✅ Konten Utama -->
      <div class="bg-white rounded-xl p-6 shadow-md space-y-6 border border-gray-200">
        <div>
          <h2 class="text-2xl font-semibold">Invoice</h2>
          <div class="text-sm text-gray-500">ID Order: {{ order.id_order }} | ID Invoice: {{ invoice.id_invoice }}</div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4 text-sm">
          <div><span class="font-semibold">Nama Pelanggan:</span> {{ order.nama_pelanggan }}</div>
          <div><span class="font-semibold">Tanggal Pembayaran:</span> {{ order.created_at }}</div>
          <div><span class="font-semibold">Jenis Invoice:</span> {{ invoice.kategori.nama }}</div>
          <div><span class="font-semibold">Status Invoice:</span> {{ invoice.status.nama }}</div>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="p-2">Produk</th>
                    <th class="p-2 text-center">Qty</th>
                    <th class="p-2 text-right">Harga</th>
                    <th class="p-2 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in order.order_detail" :key="item.id" class="border-t">
                    <td class="p-2">{{ item.nama }}</td>
                    <td class="p-2 text-center">{{ item.qty }}</td>
                    <td class="p-2 text-right">Rp. {{ item.harga.toLocaleString() }}</td>
                    <td class="p-2 text-right">Rp. {{ (item.harga * item.qty).toLocaleString() }}</td>
                </tr>
            </tbody>
        </table>

        <!-- ✅ Tambahan Produk -->
        <div v-if="order.order_detail.some(d => d.order_tambahan.length)" class="mt-4">
          <h3 class="font-semibold mb-2">Tambahan:</h3>
          <ul class="list-disc list-inside text-sm text-gray-700">
            <li v-for="item in order.order_detail" :key="item.id">
              {{
                item.order_tambahan
                  .map(tambahan => `${tambahan.nama} - Rp. ${tambahan.harga.toLocaleString()}`)
                  .join(', ')
              }}
            </li>
          </ul>
        </div>

        <!-- ✅ Keterangan -->
        <div class="pt-4 border-t space-y-1 text-sm text-gray-700">
          <div class="flex justify-between"><span>Keterangan:</span> <span>{{ order.keterangan }}</span></div>
        </div>


        <!-- ✅ Total dan Pembayaran -->
        <div class="pt-4 border-t space-y-1 text-sm text-gray-700">
          <div class="flex justify-between"><span>Sub Total:</span> <span>Rp. {{ subTotal.toLocaleString() }}</span></div>
          <div class="flex justify-between"><span>Lainnya:</span> <span>Rp. {{ order.lainnya.toLocaleString() }}</span></div>
          <div class="flex justify-between"><span>Diskon:</span> <span>- Rp. {{ order.diskon.toLocaleString() }}</span></div>
          <div class="flex justify-between font-semibold text-base border-t pt-2">
            <span>Total:</span>
            <span>Rp. {{ order.total.toLocaleString() }}</span>
          </div>
          <div class="flex justify-between"><span>Dibayarkan:</span> <span>Rp. {{ invoice.pembayaran.bayar.toLocaleString() }}</span></div>
          <div class="flex justify-between"><span>Kembalian:</span> <span>Rp. {{ invoice.pembayaran.kembalian.toLocaleString() }}</span></div>
        </div>

        <div class="text-sm text-gray-500 pt-2">Kasir: {{ order.user_nama ?? '-' }}</div>

        <!-- ✅ Alasan Pembatalan -->
        <div v-if="invoice.status.nama !== 'Selesai'" class="pt-4">
          <label for="alasanBatal" class="block text-sm font-medium text-gray-700">Alasan Pembatalan (Opsional)</label>
          <textarea
            v-model="form.keterangan"
            id="alasanBatal"
            rows="2"
            class="mt-1 block w-full rounded border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
          ></textarea>
        </div>

        <!-- ✅ Tombol Aksi -->
        <a
          :href="route('invoice.export.pdf', invoice.id_invoice)"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded hover:bg-indigo-700 transition"
        >
          📄 PDF
        </a>
        <div v-if="invoice.status.nama === 'Proses'" class="flex flex-wrap justify-end gap-2 pt-6">
          <a
            v-if="invoice.pembayaran.bukti_pembayaran"
            :href="invoice.pembayaran.bukti_pembayaran"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center px-4 py-2 bg-slate-600 text-white font-medium rounded hover:bg-slate-700 transition"
          >
            🔍 Bukti Bayar
          </a>
          <button
            type="button"
            @click="submit('batal')"
            class="px-4 py-2 font-medium rounded bg-red-600 text-white hover:bg-red-700 cursor-pointer"
          >
            Batal
          </button>
          <button
            type="button"
            @click="submit('selesai')"
            class="px-4 py-2 font-medium rounded bg-green-500 text-white hover:bg-green-700 cursor-pointer"
          >
            Konfirmasi
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
