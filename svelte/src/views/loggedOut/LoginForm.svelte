<script lang="ts">
    import {fly} from "svelte/transition";
    import {soundEffects} from "../../ui/soundEffects";

    import {createEventDispatcher} from "svelte";

    import {mainView} from "../../stores/mainView";
    import {auth} from "../../stores/auth";

    let username: string = '';
    let password: string = '';
    let loginDisabled: boolean;
    let showLoginError: boolean = false;

    const dispatch = createEventDispatcher();

    const doRegister = () => {
        soundEffects.play('click');
        dispatch('switchSubView', {view: 'register'});
    }

    const doLogin = () => {
        soundEffects.play('click');
        showLoginError = false;

        auth.login(username, password).then(() => {
            mainView.switchView('home');
        }).catch(() => {
            showLoginError = true;
        });
    }

    $: {
        loginDisabled = (! username.length || ! password.length);
    }
</script>

<main in:fly={{x: -500, delay: 350}} out:fly={{x: -500}}>
    <div class="form">
        <div>Username</div>
        <input type="text" bind:value={username} />
        <div>Password</div>
        <input type="password" bind:value={password} />
        <button type="button" disabled={loginDisabled} on:click={doLogin}>Login</button>
    </div>

    {#if showLoginError}
        <div class="loginError" transition:fly>
            <p>Sorry, those login details are invalid.</p>
        </div>
    {/if}

    <div class="h-divider">
        <div class="h-divider-line">&nbsp;</div>
        <div class="h-divider-text">OR</div>
        <div class="h-divider-line">&nbsp;</div>
    </div>

    <div class="form">
        <button class="btn-link" type="button" on:click={doRegister}>Register New Team</button>
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
    .loginError {
        border: 1px solid #81171b;
        background-color: #ad2e24;
        text-align: center;
        border-radius: .25rem;
    }
</style>
