<script lang="ts">
    import {fly} from "svelte/transition";
    import {soundEffects} from "../../ui/soundEffects";

    import {createEventDispatcher} from "svelte";

    let username: string = '';
    let password: string = '';
    let teamName: string = '';
    let registerDisabled: boolean;
    let showRegisterError: boolean = false;

    const dispatch = createEventDispatcher();

    const doRegister = () => {
        soundEffects.play('click');
    }

    const doLogin = () => {
        soundEffects.play('click');
        dispatch('switchSubView', {view: 'login'});
    }

    $: {
        registerDisabled = (! username.length || ! password.length || ! teamName.length);
    }
</script>

<main in:fly={{x: 500, delay: 350}} out:fly={{x: 500}}>

    <div class="form">
        <div>Username</div>
        <input type="text" bind:value={username} />
        <div>Password</div>
        <input type="password" bind:value={password} />
        <div>Team Name</div>
        <input type="text" bind:value={teamName} />
        <button type="button" disabled={registerDisabled} on:click={doRegister}>Register</button>
    </div>

    {#if showRegisterError}
        <div class="registerError" transition:fly>
            <p>Sorry, we couldn't process your registration.</p>
        </div>
    {/if}

    <div class="h-divider">
        <div class="h-divider-line">&nbsp;</div>
        <div class="h-divider-text">OR</div>
        <div class="h-divider-line">&nbsp;</div>
    </div>

    <div class="form">
        <button class="btn-link" type="button" on:click={doLogin}>Back to Login</button>
    </div>
</main>

<style>
    main {
        padding: 1rem;
    }
    .form {
        display: flex;
        flex-direction: column;
    }
    .form div {
        padding: 0.5rem 0;
    }
    .registerError {
        border: 1px solid #81171b;
        background-color: #ad2e24;
        text-align: center;
        border-radius: .25rem;
    }
</style>
