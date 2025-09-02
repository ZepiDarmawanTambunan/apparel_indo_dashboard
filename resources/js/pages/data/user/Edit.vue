<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {Select} from 'primevue';

interface Pegawai {
  id: number;
  id_pegawai?: string;
  nama: string;
  email?: string;
  nohp?: string;
  jabatan?: string;
  foto_url?: string;
}

interface Role {
    id: number;
    name: string;
}

interface User {
  id: number;
  nama: string;
  pegawai_id?: number | null;
  pegawai?: Pegawai | null;
  role_id?: number | null;
}

const props = defineProps<{
    user: User,
    roles: Role[],
}>();

const form = useForm({
  nama: props.user.nama ?? '',
  password: '',
  email: props.user.pegawai?.email ?? '',
  nohp: props.user.pegawai?.nohp ?? '',
  jabatan: props.user.pegawai?.jabatan ?? '',
  role_id: props.user.role_id,
  foto_pegawai: null as File | null,
});

const fotoLama = ref(props.user.pegawai?.foto_url ?? null);

const fotoBaruPreview = ref<string | null>(null);

function onFileChange(event: Event) {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    form.foto_pegawai = target.files[0];
    fotoBaruPreview.value = URL.createObjectURL(target.files[0]);
  } else {
    form.foto_pegawai = null;
    fotoBaruPreview.value = null;
  }
}

function submit() {
    form.transform((data) => {
      const formData = new FormData();

      formData.append('_method', 'PUT');
      formData.append('nama', data.nama);
      formData.append('password', data.password);
      if (form.password) {
        formData.append('password', data.password);
      }
      formData.append('email', data.email);
      formData.append('nohp', data.nohp);
      formData.append('jabatan', data.jabatan);
      formData.append('role_id', data.role_id != null ? String(data.role_id) : '');

      if (data.foto_pegawai) {
        formData.append('foto_pegawai', data.foto_pegawai);
      }

      return formData;
    })
    .post(route('data.user.update', props.user.id), {
      replace: true,
      forceFormData: true,
      onSuccess: () => {
      },
      onError: (errors) => {
        console.error('Error saat submit:', errors);
      },
    });
}
</script>

<template>
  <Head :title="`Edit User & Pegawai - ${props.user.nama}`" />

  <AppLayout>
    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-2xl font-semibold mb-6">Edit User & Pegawai</h2>

        <form @submit.prevent="submit" class="space-y-4" enctype="multipart/form-data">
          <div>
            <label class="block mb-1 font-medium">Nama</label>
            <input v-model="form.nama" type="text" class="w-full border rounded px-3 py-2" />
            <span v-if="form.errors.nama" class="text-red-500 text-sm">{{ form.errors.nama }}</span>
          </div>

          <div>
            <label class="block mb-1 font-medium">Password <small>(kosongkan jika tidak ganti)</small></label>
            <input v-model="form.password" type="password" class="w-full border rounded px-3 py-2" />
            <span v-if="form.errors.password" class="text-red-500 text-sm">{{ form.errors.password }}</span>
          </div>

          <div>
            <label class="block mb-1 font-medium">Email</label>
            <input v-model="form.email" type="email" class="w-full border rounded px-3 py-2" />
            <span v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</span>
          </div>

          <div>
            <label class="block mb-1 font-medium">No. HP</label>
            <input v-model="form.nohp" type="text" class="w-full border rounded px-3 py-2" />
            <span v-if="form.errors.nohp" class="text-red-500 text-sm">{{ form.errors.nohp }}</span>
          </div>

          <div>
            <label class="block mb-1 font-medium">Jabatan</label>
            <input v-model="form.jabatan" type="text" class="w-full border rounded px-3 py-2" />
            <span v-if="form.errors.jabatan" class="text-red-500 text-sm">{{ form.errors.jabatan }}</span>
          </div>

        <div>
            <label class="block mb-1 font-medium">Role</label>
            <Select
                v-model="form.role_id"
                :options="roles"
                filter
                :filterFields="['id', 'name']"
                optionLabel="name"
                optionValue="id"
                placeholder="Pilih Role"
                :disabled="roles.length === 0"
                class="w-full"
                required
            />
            <span v-if="form.errors.role_id" class="text-red-500 text-sm">{{ form.errors.role_id }}</span>
        </div>
        <div>
            <label class="block mb-1 font-medium">Foto Pegawai</label>
            <input
                type="file"
                accept="image/*"
                @change="onFileChange"
                class="w-full border rounded px-3 py-2 text-sm file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-gray-100 file:text-gray-700"
            />
            <span v-if="form.errors.foto_pegawai" class="text-red-500 text-sm">{{ form.errors.foto_pegawai }}</span>
        </div>

          <div v-if="fotoBaruPreview" class="mt-4">
            <p class="font-medium mb-2">Preview Foto Baru:</p>
            <img :src="fotoBaruPreview" class="w-32 h-32 object-cover rounded border" />
          </div>

          <div v-else-if="fotoLama" class="mt-4">
            <p class="font-medium mb-2">Foto Pegawai Saat Ini:</p>
            <img :src="fotoLama" class="w-32 h-32 object-cover rounded border" />
          </div>

          <div class="pt-4">
            <button
              type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md cursor-pointer"
              :disabled="form.processing"
            >
              Simpan perubahan
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
