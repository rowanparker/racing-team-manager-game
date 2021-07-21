<script lang="ts">
import {fly} from "svelte/transition";
import {auth} from "../stores/auth";

import LoginForm from './loggedOut/LoginForm.svelte';
import RegisterForm from './loggedOut/RegisterForm.svelte';
import {onMount} from "svelte";

let subView: string = 'login';

const switchSubView = (event) => {
    subView = event.detail.view;
}

onMount(() => {
    if (auth.pendingLogout) {
        setTimeout(auth.logout, 1000);
    }
})

</script>

<main in:fly={{x: 500, delay: 350}} out:fly={{x: 500}}>
    <div class="logo">
        <img src="/art/logo/375x211.png" alt="Main Logo" />
    </div>

    {#if subView === 'login'}
        <LoginForm on:switchSubView={switchSubView} />
    {:else if subView === 'register'}
        <RegisterForm on:switchSubView={switchSubView} />
    {/if}
</main>

<style>
.login div {
    padding: 0.5rem 0;
}
.logo {
    margin-bottom: 2.5rem;
}
.logo img {
    width: 100%;
}
</style>
