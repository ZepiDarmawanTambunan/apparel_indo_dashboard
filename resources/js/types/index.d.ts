import type { PageProps } from '@inertiajs/core';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
}

// Disesuaikan dengan model User
export interface User {
    id: number;
    nama: string;
    pegawai_id: string;
    created_at: string;
    updated_at: string;
    pegawai?: Pegawai | null; // relasi
}

// Disesuaikan dengan model Pegawai
export interface Pegawai {
    id_pegawai: string;
    nama: string;
    email: string | null;
    nohp: string | null;
    jabatan: string | null;
    created_at: string;
    updated_at: string;
    user?: User | null;
}

export type BreadcrumbItemType = BreadcrumbItem;
