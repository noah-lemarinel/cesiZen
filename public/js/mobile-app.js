// Very small mobile SPA "app" that uses the simple JSON API added in the backend.
// It uses fetch() and progressively enhances the page. No build step required.

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('mobile-root');

    const state = { emotions: [], entries: [], exercises: [] };

    function el(tag, attrs = {}, ...children) {
        const node = document.createElement(tag);
        for (const [k, v] of Object.entries(attrs)) {
            if (k === 'class') node.className = v;
            else if (k.startsWith('on') && typeof v === 'function') node.addEventListener(k.substr(2), v);
            else node.setAttribute(k, v);
        }
        for (const c of children) node.append(typeof c === 'string' ? document.createTextNode(c) : c);
        return node;
    }

    async function fetchJson(path, opts = {}) {
        const res = await fetch(path, Object.assign({credentials: 'same-origin', headers: {'Accept': 'application/json'}}, opts));
        if (!res.ok) throw new Error('HTTP '+res.status);
        return res.json();
    }

    async function loadData() {
        try {
            state.emotions = await fetchJson('/api/emotions');
            state.exercises = await fetchJson('/api/exercises');
            try {
                state.entries = await fetchJson('/api/entries');
            } catch (e) {
                // user might not be logged in — that's ok
                state.entries = [];
            }
            renderApp();
        } catch (e) {
            root.innerHTML = '';
            root.appendChild(el('div', {class: 'text-red-600'}, 'Impossible de charger les données.'));
            console.error(e);
        }
    }

    function renderApp() {
        root.innerHTML = '';

        const entriesList = el('div', {}, el('h3', {class: 'text-lg font-medium mb-2'}, 'Vos entrées'));
        if (state.entries.length === 0) {
            entriesList.appendChild(el('div', {class: 'mb-3 text-sm text-gray-600'}, 'Aucune entrée — connectez-vous pour en créer.'));
        } else {
            const ul = el('ul', {class: 'space-y-2 mb-3'});
            for (const e of state.entries) {
                ul.appendChild(el('li', {class: 'p-2 border rounded'}, `${e.created_at ?? ''} — ${e.emotion} ${e.notes ? ' — ' + e.notes : ''}`));
            }
            entriesList.appendChild(ul);
        }

        const createForm = el('form', {class: 'mb-4'});
        const select = el('select', {name: 'emotion_id', class: 'w-full p-2 rounded border mb-2'});
        select.appendChild(el('option', {value: ''}, 'Choisir une émotion'));
        for (const em of state.emotions) {
            select.appendChild(el('option', {value: em.id}, em.name));
        }
        const notes = el('textarea', {name: 'notes', class: 'w-full p-2 rounded border mb-2', rows: 3});
        const submit = el('button', {class: 'px-4 py-2 bg-primary text-white rounded'}, 'Ajouter');

        createForm.appendChild(select);
        createForm.appendChild(notes);
        createForm.appendChild(submit);

        createForm.addEventListener('submit', async (ev) => {
            ev.preventDefault();
            const body = { emotion_id: select.value, notes: notes.value };
            try {
                const res = await fetchJson('/api/entries', {method: 'POST', body: JSON.stringify(body), headers: {'Content-Type': 'application/json'}});
                // reload entries
                await loadData();
            } catch (e) {
                alert('Impossible d\'ajouter l\'entrée — êtes-vous connecté ?');
                console.error(e);
            }
        });

        const exercisesList = el('div', {}, el('h3', {class: 'text-lg font-medium mb-2'}, 'Exercices'));
        const exUl = el('ul', {class: 'space-y-2 mb-3'});
        for (const ex of state.exercises) {
            exUl.appendChild(el('li', {class: 'p-2 border rounded'}, el('strong', {}, ex.title), el('div', {}, ex.description ?? '')));
        }
        exercisesList.appendChild(exUl);

        // Login / account hint
        const authHint = el('div', {class: 'mb-4 text-sm'}, 'Pour voir et créer des entrées vous devez être connecté. Utilisez la page de connexion du site.');

        root.appendChild(entriesList);
        root.appendChild(createForm);
        root.appendChild(exercisesList);
        root.appendChild(authHint);
    }

    loadData();
});

