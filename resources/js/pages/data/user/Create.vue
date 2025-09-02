<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import {Select} from 'primevue';
import { ref } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';

interface Role {
    id: number;
    name: string;
}

const props = defineProps<{
  roles: Role[];
}>();

const { roles } = props;
const showPassword = ref(false);

const form = useForm({
  nama: '',
  password: '123456',
  email: '',
  nohp: '',
  jabatan: '',
  role_id: '',
  foto_pegawai: null as File | null,
});

function submit() {
  const data = new FormData();

  data.append('nama', form.nama);
  data.append('password', form.password);
  data.append('email', form.email ?? '');
  data.append('nohp', form.nohp ?? '');
  data.append('jabatan', form.jabatan ?? '');
  data.append('role_id', form.role_id ?? '');
  if (form.foto_pegawai) {
    data.append('foto_pegawai', form.foto_pegawai);
  }

  form.post(route('data.user.store'), {
    replace: true,
    forceFormData: true,
    onSuccess: () => {
    },
    onError: (errors) => {
      console.error('Error saat submit:', errors);
    },
  });
}

function onFileChange(event: Event) {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    form.foto_pegawai = target.files[0];
  }
}

function getObjectURL(file: File) {
  return URL.createObjectURL(file);
}
</script>

<template>
  <Head title="Buat User & Pegawai" />

  <AppLayout>
    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-2xl font-semibold mb-6">Buat User & Pegawai</h2>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block mb-1 font-medium">Nama</label>
            <input v-model="form.nama" type="text" class="w-full border rounded px-3 py-2" />
            <span v-if="form.errors.nama" class="text-red-500 text-sm">{{ form.errors.nama }}</span>
          </div>

            <div>
                <label class="block mb-1 font-medium">Password</label>
                <div class="relative">
                    <input
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        class="w-full border rounded px-3 py-2" />
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 flex items-center pr-3"
                        @click="showPassword = !showPassword"
                        tabindex="-1"
                        >
                        <Eye v-if="!showPassword" class="w-5 h-5 text-gray-500" />
                        <EyeOff v-else class="w-5 h-5 text-gray-500" />
                    </button>
                    <span v-if="form.errors.password" class="text-red-500 text-sm">{{ form.errors.password }}</span>
                </div>
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
                required
                class="w-full border rounded px-3 py-2 text-sm file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-gray-100 file:text-gray-700"
            />
            <span v-if="form.errors.foto_pegawai" class="text-red-500 text-sm">{{ form.errors.foto_pegawai }}</span>
          </div>

          <div v-if="form.foto_pegawai" class="mt-4">
            <p class="font-medium mb-2">Preview Foto:</p>
            <img :src="getObjectURL(form.foto_pegawai)" class="w-32 h-32 object-cover rounded border" />
          </div>

          <div class="pt-4">
            <button
              type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md cursor-pointer"
              :disabled="form.processing"
            >
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
