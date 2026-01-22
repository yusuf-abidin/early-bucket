<!-- Login.vue -->
<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/login';
import { Form, Head } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <AuthBase
        title="Selamat Datang"
        description="Masuk ke sistem manajemen inventori perusahaan"
    >
        <Head title="Login" />

        <div class="mx-auto w-full max-w-md">
            <!-- Logo Perusahaan + Nama Perusahaan -->
            <div class="mb-8 flex flex-col items-center">
                <div
                    class="mb-4 rounded-full bg-white p-4 shadow-lg dark:bg-gray-800"
                >
                    <!-- Ganti dengan logo perusahaan Anda -->
                    <!-- Contoh: gunakan SVG atau <img src="/logo.png" /> -->
                    <!--                    <svg-->
                    <!--                        class="h-16 w-16 text-primary"-->
                    <!--                        fill="currentColor"-->
                    <!--                        viewBox="0 0 24 24"-->
                    <!--                    >-->
                    <!--                        <path-->
                    <!--                            d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"-->
                    <!--                        />-->
                    <!--                    </svg>-->
                    <img
                        src="/favicon-btn.svg"
                        class="h-16 w-auto"
                        alt="Logo"
                    />
                    <!-- Alternatif: <img src="/images/company-logo.png" alt="Company Logo" class="h-16 w-auto" /> -->
                </div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Early Bucket
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Sistem Manajemen Pekerjaan Early Bucket
                </p>
            </div>

            <!-- Status Message -->
            <div
                v-if="status"
                class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-center text-sm font-medium text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-200"
            >
                {{ status }}
            </div>

            <!-- Form Login -->
            <Form
                v-bind="store.form()"
                :reset-on-success="['password']"
                v-slot="{ errors, processing }"
                class="space-y-6"
            >
                <!-- Email -->
                <div class="space-y-2">
                    <Label for="email" class="text-gray-700 dark:text-gray-300"
                        >Email</Label
                    >
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="nama@gmail.com"
                        class="h-11"
                    />
                    <InputError :message="errors.email" />
                </div>

                <!-- Password -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <Label
                            for="password"
                            class="text-gray-700 dark:text-gray-300"
                            >Password</Label
                        >
                    </div>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="h-11"
                    />
                    <InputError :message="errors.password" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <Checkbox id="remember" name="remember" class="h-5 w-5" />
                    <Label
                        for="remember"
                        class="ml-2 text-sm text-gray-600 dark:text-gray-400"
                    >
                        Ingat saya
                    </Label>
                </div>

                <!-- Submit Button -->
                <Button
                    type="submit"
                    class="h-11 w-full bg-primary text-base font-medium transition-colors hover:bg-primary/90"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" class="mr-2" />
                    Masuk
                </Button>
            </Form>
        </div>

        <!-- Footer (opsional, bisa dihapus jika tidak perlu) -->
        <div class="mt-10 text-center text-xs text-gray-500 dark:text-gray-400">
            © {{ new Date().getFullYear() }} Early Bucket - CRSD1 • All rights reserved
        </div>
    </AuthBase>
</template>
