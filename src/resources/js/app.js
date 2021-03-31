import { createApp } from 'vue';

import ExampleComponent from './components/ExampleComponent';

const app = createApp({});
app.component('example-component', ExampleComponent);

app.mount('#app');
