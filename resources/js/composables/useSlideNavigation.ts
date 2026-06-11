const navigationPosition = (pathname: string): number | null => {
    if (['/', '/home', '/dashboard'].includes(pathname)) {
        return 0;
    }

    if (pathname.startsWith('/books')) {
        return 1;
    }

    if (pathname.startsWith('/loans')) {
        return 2;
    }

    if (pathname.startsWith('/notifications')) {
        return 3;
    }

    if (pathname.startsWith('/admin')) {
        return 4;
    }

    return null;
};

export const setSlideDirection = (targetPosition: number) => {
    const currentPosition = navigationPosition(location.pathname);

    if (currentPosition === null || currentPosition === targetPosition) {
        document.documentElement.dataset.slideDirection = 'neutral';
        return;
    }

    document.documentElement.dataset.slideDirection =
        targetPosition > currentPosition ? 'forward' : 'backward';
};
