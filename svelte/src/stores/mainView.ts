import LoggedOut from '../views/LoggedOut.svelte';
import Home from './../views/Home.svelte';

import { writable } from "svelte/store";

const createMainView = () => {

    const views = new Map();
    views.set('loggedOut', LoggedOut);
    views.set('home', Home);

    const { subscribe, set } = writable(null);

    return {
        subscribe,
        switchView: (key) => set(views.get(key)),
    }
};

export const mainView = createMainView();
