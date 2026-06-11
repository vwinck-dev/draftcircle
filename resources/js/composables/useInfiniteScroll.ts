import { onBeforeUnmount, onMounted, type Ref } from 'vue';

export const useInfiniteScroll = (target: Ref<Element | null>, callback: () => void) => {
    const observer = new IntersectionObserver(
        (entries) => entries[0]?.isIntersecting && callback(),
    );
    onMounted(() => target.value && observer.observe(target.value));
    onBeforeUnmount(() => observer.disconnect());
};
