import './bootstrap';
import { createApp } from 'vue';
import Login from './Components/Login.vue';
import Register from './Components/Register.vue';
import PhaserGame from './Components/PhaserGame.vue';

// 現在のパスに基づいて適切なコンポーネントをマウント
const path = window.location.pathname;
let component;

if (path === '/login') {
    component = Login;
} else if (path === '/register') {
    component = Register;
} else if (path === '/') {
    component = PhaserGame;
}

if (component) {
    const app = createApp(component);
    app.mount('#app');
}
