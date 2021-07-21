const createSoundEffects = () => {

    const effects = new Map();
    effects.set('click', 'audio/click.mp3');

    return {
        play: (key) => {
            let audio = new Audio(effects.get(key));
            audio.play();
        }
    }
};

export const soundEffects = createSoundEffects();
