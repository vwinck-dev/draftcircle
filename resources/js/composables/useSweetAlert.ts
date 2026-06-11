import Swal, { type SweetAlertIcon } from 'sweetalert2';
import { useSoundEffects } from './useSoundEffects';

const sounds = useSoundEffects();

type ConfirmOptions = {
    title: string;
    text?: string;
    confirmButtonText: string;
    cancelButtonText: string;
    icon?: SweetAlertIcon;
    destructive?: boolean;
};

const themeOptions = () => {
    const dark = document.documentElement.classList.contains('dark');

    return {
        background: dark ? '#0f172a' : '#ffffff',
        color: dark ? '#e2e8f0' : '#1e293b',
    };
};

export const showToast = (message: string, icon: SweetAlertIcon = 'success') => {
    if (icon === 'success') sounds.success();
    else if (icon === 'error') sounds.error();
    return Swal.fire({
        ...themeOptions(),
        toast: true,
        position: 'top-end',
        icon,
        title: message,
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
    });
};

export const confirmAction = async ({
    title,
    text,
    confirmButtonText,
    cancelButtonText,
    icon = 'warning',
    destructive = false,
}: ConfirmOptions): Promise<boolean> => {
    const result = await Swal.fire({
        ...themeOptions(),
        title,
        text,
        icon,
        showCancelButton: true,
        confirmButtonText,
        cancelButtonText,
        confirmButtonColor: destructive ? '#e11d48' : '#2563eb',
        cancelButtonColor: '#64748b',
        reverseButtons: true,
        focusCancel: destructive,
    });

    return result.isConfirmed;
};
