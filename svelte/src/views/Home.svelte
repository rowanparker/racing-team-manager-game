<script lang="ts">
import {fly} from "svelte/transition";
import {currentUser} from "../stores/currentUser";
import {auth} from "../stores/auth";
import {soundEffects} from "../ui/soundEffects";
import { mainView } from "../stores/mainView";

const logout = () => {
    soundEffects.play('click');
    auth.pendingLogout = true;
    mainView.switchView('loggedOut');
}

</script>

<main in:fly="{{delay: 350}}" out:fly>
    <nav>
        <div class="title">Boi Racer</div>
        <button type="button" on:click={logout}>Logout</button>
    </nav>
    <div style="padding: 1rem;">
        Logged in as <i>{$currentUser.username}</i>
    </div>
</main>

<style>
    nav {
        border-bottom: 1px solid #81171b;
        background-color: #ad2e24;
        display: flex;
        justify-content: space-between;
        padding: 0.25rem;
    }
    nav button {
        border: 0;
        background-color: #540804;
        color: #fff;
        font-size: 0.75rem;
        margin: 0;
    }
    nav .title {
        font-weight: bold;
        font-size: 1.5rem;
    }
</style>
