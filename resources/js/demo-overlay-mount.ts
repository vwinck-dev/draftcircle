import { createApp } from 'vue';
import DemoOverlay from './Components/DemoOverlay.vue';

const root = document.getElementById('demo-root');
if (root) {
    createApp(DemoOverlay).mount(root);
}
