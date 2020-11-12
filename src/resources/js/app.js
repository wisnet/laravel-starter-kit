import { createApp } from 'vue';

import ExampleComponent from "./ExampleComponent";

const app = createApp({});
app.mount('#app');

app.component('example-component', ExampleComponent);