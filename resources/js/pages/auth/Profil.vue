<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import {ref} from 'vue';
import {ConfirmDialog } from 'primevue';

interface Pegawai {
  id: number;
  id_pegawai?: string;
  nama: string;
  email?: string;
  nohp?: string;
  jabatan?: string;
  foto_url?: string;
}

interface User {
  id: number;
  nama: string;
  pegawai_id?: number | null;
  pegawai?: Pegawai | null;
  role?: string | null;
}

const props = defineProps<{
    user: User,
}>();

const form = useForm({
  nama: props.user.nama ?? '',
  password: '',
  email: props.user.pegawai?.email ?? '',
  nohp: props.user.pegawai?.nohp ?? '',
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
        if (data.foto_pegawai) {
            formData.append('foto_pegawai', data.foto_pegawai);
        }
        return formData;
    })
    .post(route('profil.update'), {
        replace: true,
        preserveState: true,
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.reset('password');
        },
        onError: (errors) => {
            console.error('Error saat submit:', errors);
        },
    });
}
</script>

<template>
    <Head :title="props.user.nama" />
    <AppLayout>
        <form @submit.prevent="submit">
            <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition">
                    <h2 class="text-2xl font-semibold mb-6">Profil</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 space-y-4">
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
                            <label class="block mb-1 font-medium">Role</label>
                            <input type="text" :value="props.user.role ?? '-'" class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-500 cursor-not-allowed" readonly />
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Jabatan</label>
                            <input type="text" :value="props.user.pegawai?.jabatan ?? '-'" class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-500 cursor-not-allowed" readonly />
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

                        <div v-if="fotoBaruPreview">
                            <p class="font-medium mb-2">Preview Foto Baru:</p>
                            <img :src="fotoBaruPreview" class="w-32 h-32 object-cover rounded border" />
                        </div>

                        <div v-else-if="fotoLama">
                            <p class="font-medium mb-2">Foto Pegawai Saat Ini:</p>
                            <img :src="fotoLama" class="w-32 h-32 object-cover rounded border" />
                        </div>
                    </div>
                    <div class="pt-4 flex justify-end">
                        <button
                            type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 font-medium rounded-md cursor-pointer"
                            :disabled="form.processing"
                        >
                        Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <ConfirmDialog />
    </AppLayout>
</template>
