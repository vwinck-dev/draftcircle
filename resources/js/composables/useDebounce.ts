export const useDebounce = <T extends (...args: never[]) => void>(callback: T, delay = 300) => {
    let timer: ReturnType<typeof setTimeout>;

    return (...args: Parameters<T>) => {
        clearTimeout(timer);
        timer = setTimeout(() => callback(...args), delay);
    };
};
