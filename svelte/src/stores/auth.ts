import {currentUser} from "./currentUser";

const createAuth = () => {

    const login = (username: string, password: string) => {
        currentUser.set(null);
        return fetch('/login', {
            method: 'POST',
            headers: {
                'content-type': 'application/json',
            },
            body: JSON.stringify({
                username: username,
                password: password,
            })
        }).then(r => {
            if ( ! r.ok) {
                throw new Error('Login failed');
            }
            return r.json();
        }).then(async j => {

            await fetch(j.user).then(r => r.json()).then(j => {
                currentUser.set(j);
            })
            return j;
        })
    }

    const logout = () => {
        currentUser.set(null);
        fetch('/logout');
    }

    return {
        login: login,
        logout: logout,
        pendingLogout: false,
    }
};

export const auth = createAuth();
