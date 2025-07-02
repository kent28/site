
function parseTime(str) {
    const [d, h, m] = str.split("/").map(Number);
    return ((d * 24 + h) * 60 + m) * 60; // в секундах
}

function formatTime(sec) {
    const h = Math.floor(sec / 3600).toString().padStart(2, '0');
    const m = Math.floor((sec % 3600) / 60).toString().padStart(2, '0');
    const s = (sec % 60).toString().padStart(2, '0');
    return `${h}:${m}:${s}`;
}

document.addEventListener("DOMContentLoaded", () => {
    const entries = document.querySelectorAll(".slider .entry");

    const state = [];

    entries.forEach(entry => {
        const open = parseTime(entry.dataset.open);
        const dur = parseTime(entry.dataset.duration);
        const stay = parseTime(entry.dataset.stay);
        const statusEl = entry.querySelector(".status");
        const countdownEl = entry.querySelector(".countdown");

        state.push({
            el: entry,
            statusEl,
            countdownEl,
            open,
            dur,
            stay,
            total: open + dur + stay,
            time: 0
        });
    });

    function tick() {
        state.forEach(obj => {
            const { statusEl, countdownEl, open, dur, stay, time, total } = obj;
            let text = "", cls = "";
            if (time < open) {
                text = "⏳ Ждёт открытия"; cls = "waiting";
                countdownEl.textContent = formatTime(open - time);
            } else if (time < open + dur) {
                text = "✅ Открыт"; cls = "open";
                countdownEl.textContent = formatTime(open + dur - time);
            } else if (time < open + dur + stay) {
                text = "❌ Закрыт, но вы ещё внутри"; cls = "closed";
                countdownEl.textContent = formatTime(open + dur + stay - time);
            } else {
                text = "⛔ Время вышло"; cls = "ended";
                countdownEl.textContent = "00:00:00";
            }

            statusEl.textContent = text;
            statusEl.className = "status " + cls;

            if (obj.time <= obj.total) obj.time++;
        });

        if (state.some(obj => obj.time <= obj.total)) {
            setTimeout(tick, 1000);
        }
    }

    tick();
});
