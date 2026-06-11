let ctx: AudioContext | null = null;

const getCtx = (): AudioContext => {
    if (!ctx) ctx = new AudioContext();
    return ctx;
};

const play = (
    frequency: number,
    duration: number,
    type: OscillatorType = 'sine',
    gain = 0.08,
    endFrequency?: number,
) => {
    try {
        const ac = getCtx();
        const osc = ac.createOscillator();
        const gainNode = ac.createGain();

        osc.connect(gainNode);
        gainNode.connect(ac.destination);

        osc.type = type;
        osc.frequency.setValueAtTime(frequency, ac.currentTime);
        if (endFrequency !== undefined) {
            osc.frequency.exponentialRampToValueAtTime(endFrequency, ac.currentTime + duration);
        }

        gainNode.gain.setValueAtTime(gain, ac.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.001, ac.currentTime + duration);

        osc.start(ac.currentTime);
        osc.stop(ac.currentTime + duration);
    } catch {
        // AudioContext may be blocked before user interaction — silent fail is correct
    }
};

export const useSoundEffects = () => ({
    /** Subtle navigation click */
    tick: () => play(600, 0.06, 'sine', 0.05),

    /** Success / form saved */
    success: () => {
        play(440, 0.1, 'sine', 0.07);
        setTimeout(() => play(660, 0.15, 'sine', 0.06), 80);
    },

    /** Error / validation failure */
    error: () => play(180, 0.25, 'sine', 0.08, 120),

    /** Notification cleared / like */
    pop: () => play(800, 0.08, 'sine', 0.06, 600),

    /** Modal open */
    open: () => play(300, 0.12, 'sine', 0.04, 400),

    /** Modal close / dismiss */
    dismiss: () => play(400, 0.1, 'sine', 0.04, 280),
});
