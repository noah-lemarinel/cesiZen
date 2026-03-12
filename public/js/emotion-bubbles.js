// Simple emotion bubble visualizer
// Uses window.EMOTIONS (exported by the Twig template)
(function () {
    if (!window.EMOTIONS) return;

    const container = document.getElementById('days-visuals');
    if (!container) return;

    // Create synthetic previous days — in a real app you'd fetch entries
    const days = [];
    const today = new Date();
    for (let d = 0; d < 6; d++) {
        const date = new Date(today);
        date.setDate(today.getDate() - d - 1);
        // choose a random subset of emotions for that day
        const sample = window.EMOTIONS.slice().sort(() => 0.5 - Math.random()).slice(0, 1 + Math.floor(Math.random() * Math.min(3, window.EMOTIONS.length)));
        days.push({date: date.toISOString().slice(0, 10), emotions: sample});
    }

    // Utility: mix colors for a legend background (fallback)
    function hexToRgba(hex, a = 1) {
        const h = hex.replace('#', '');
        const bigint = parseInt(h, 16);
        const r = (bigint >> 16) & 255;
        const g = (bigint >> 8) & 255;
        const b = bigint & 255;
        return `rgba(${r}, ${g}, ${b}, ${a})`;
    }

    function createDayCard(day) {
        const card = document.createElement('div');
        card.className = 'rounded bg-white/50 dark:bg-gray-700 p-3 shadow cursor-pointer hover:scale-[1.01] transition-transform';

        const title = document.createElement('div');
        title.className = 'flex items-center justify-between mb-2';
        title.innerHTML = `<div class="font-medium">${day.date}</div><div class="text-xs text-gray-500">${day.emotions.length} émotions</div>`;
        card.appendChild(title);

        // canvas visualization
        const canvasWrap = document.createElement('div');
        canvasWrap.className = 'w-full h-40 relative';
        const canvas = document.createElement('canvas');
        canvas.width = 400;
        canvas.height = 160;
        canvas.style.width = '100%';
        canvas.style.height = '160px';
        canvasWrap.appendChild(canvas);
        card.appendChild(canvasWrap);

        // small legend
        const legend = document.createElement('div');
        legend.className = 'flex gap-2 items-center mt-3 flex-wrap';
        day.emotions.forEach(e => {
            const sw = document.createElement('div');
            sw.className = 'flex items-center gap-2 mr-2';
            sw.innerHTML = `<span class="w-3 h-3 rounded-full" style="background:${e.color}"></span><span class="text-xs">${e.name}</span>`;
            legend.appendChild(sw);
        });
        card.appendChild(legend);

        // draw bubbles
        const ctx = canvas.getContext('2d');

        function draw() {
            // clear
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            // set blending for nice color mixing
            ctx.globalCompositeOperation = 'lighter';

            const bubbles = [];
            const w = canvas.width;
            const h = canvas.height;
            // generate bubble positions
            day.emotions.forEach((em, i) => {
                const r = 30 + Math.random() * 40;
                const x = (i + 1) * (w / (day.emotions.length + 1)) + (Math.random() - 0.5) * 40;
                const y = h / 2 + (Math.random() - 0.5) * 40;
                bubbles.push({x, y, r, color: hexToRgba(em.color, 0.9), vx: (Math.random() - 0.5) * 0.6, vy: (Math.random() - 0.5) * 0.6});
            });

            // simple animation frame
            let t = 0;
            function frame() {
                t += 1;
                ctx.clearRect(0, 0, w, h);
                ctx.globalCompositeOperation = 'lighter';
                bubbles.forEach(b => {
                    // gentle float
                    b.x += b.vx + Math.sin(t / 50 + b.r) * 0.2;
                    b.y += b.vy + Math.cos(t / 50 + b.r) * 0.2;
                    // bounds
                    if (b.x - b.r < 0) b.x = b.r;
                    if (b.x + b.r > w) b.x = w - b.r;
                    if (b.y - b.r < 0) b.y = b.r;
                    if (b.y + b.r > h) b.y = h - b.r;

                    const grd = ctx.createRadialGradient(b.x - b.r / 3, b.y - b.r / 3, b.r * 0.1, b.x, b.y, b.r);
                    grd.addColorStop(0, b.color.replace(/rgba\(([^,]+),([^,]+),([^,]+),[^)]+\)/, 'rgba($1,$2,$3,0.95)'));
                    grd.addColorStop(1, b.color.replace(/rgba\(([^,]+),([^,]+),([^,]+),[^)]+\)/, 'rgba($1,$2,$3,0.35)'));

                    ctx.fillStyle = grd;
                    ctx.beginPath();
                    ctx.arc(b.x, b.y, b.r, 0, Math.PI * 2);
                    ctx.fill();
                });
                // subtle overlay to create a 'mixed' center
                requestAnimationFrame(frame);
            }

            frame();
        }

        // Kick off drawing after inserting to DOM
        setTimeout(draw, 30);

        // Click to expand (simple toggle)
        let expanded = false;
        card.addEventListener('click', () => {
            expanded = !expanded;
            if (expanded) {
                canvas.style.height = '320px';
                canvas.height = 320;
            } else {
                canvas.style.height = '160px';
                canvas.height = 160;
            }
        });

        return card;
    }

    // render day cards
    days.forEach(day => {
        const card = createDayCard(day);
        container.appendChild(card);
    });

})();


