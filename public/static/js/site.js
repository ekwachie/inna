const icons = () => window.lucide?.createIcons();
icons();

const themeButton = document.querySelector('.theme-toggle');
const systemTheme = matchMedia('(prefers-color-scheme: dark)');
let explicitTheme = false;
try { explicitTheme = ['light', 'dark'].includes(localStorage.getItem('inna-theme')); } catch {}
const syncTheme = () => {
    const dark = document.documentElement.dataset.theme === 'dark';
    themeButton?.setAttribute('aria-pressed', String(dark));
    themeButton?.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
    if (themeButton) themeButton.title = dark ? 'Switch to light mode' : 'Switch to dark mode';
};
themeButton?.addEventListener('click', () => {
    const theme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    document.documentElement.dataset.theme = theme;
    explicitTheme = true;
    try { localStorage.setItem('inna-theme', theme); } catch {}
    syncTheme();
});
systemTheme.addEventListener('change', event => {
    if (!explicitTheme) { document.documentElement.dataset.theme = event.matches ? 'dark' : 'light'; syncTheme(); }
});
addEventListener('storage', event => {
    if (event.key !== 'inna-theme') return;
    explicitTheme = ['light', 'dark'].includes(event.newValue);
    document.documentElement.dataset.theme = explicitTheme ? event.newValue : (systemTheme.matches ? 'dark' : 'light');
    syncTheme();
});
syncTheme();

document.addEventListener('click', async event => {
    const button = event.target.closest('[data-copy]');
    if (!button) return;
    const text = button.dataset.copy || button.closest('.fence')?.querySelector('code')?.textContent;
    if (!text) return;
    const status = document.querySelector('#copy-status') ?? document.body.appendChild(Object.assign(document.createElement('div'), {id: 'copy-status', className: 'copy-status'}));
    status.setAttribute('role', 'status');
    try {
        await navigator.clipboard.writeText(text);
        button.innerHTML = '<i data-lucide="check"></i>';
        status.textContent = 'Copied to clipboard';
    } catch {
        status.textContent = 'Could not copy. Select the code and copy it manually.';
    }
    icons();
    clearTimeout(button.copyTimer);
    button.copyTimer = setTimeout(() => {
        button.innerHTML = '<i data-lucide="copy"></i>';
        status.textContent = '';
        icons();
    }, 2400);
});

document.querySelectorAll('#doc-content pre > code').forEach(code => {
    const pre = code.parentElement;
    const fence = document.createElement('div');
    fence.className = 'fence';
    const head = document.createElement('div');
    head.className = 'fence-head';
    const language = document.createElement('span');
    language.textContent = [...code.classList].find(name => name.startsWith('language-'))?.slice(9) || 'Code';
    const button = document.createElement('button');
    button.type = 'button';
    button.dataset.copy = '';
    button.title = 'Copy code';
    button.setAttribute('aria-label', 'Copy code');
    button.innerHTML = '<i data-lucide="copy"></i>';
    head.append(language, button);
    pre.before(fence);
    fence.append(head, pre);
});

const menu = document.querySelector('.menu-toggle');
const sidebar = document.querySelector('.sidebar');
const setMenu = open => {
    document.body.classList.toggle('nav-open', open);
    menu?.setAttribute('aria-expanded', String(open));
};
menu?.addEventListener('click', () => setMenu(!document.body.classList.contains('nav-open')));
document.addEventListener('click', event => {
    if (menu && innerWidth <= 700 && !sidebar.contains(event.target) && !menu.contains(event.target) && !event.target.closest('.search')) setMenu(false);
});

const searchData = document.querySelector('#doc-search');
if (searchData) {
    const texts = JSON.parse(searchData.textContent);
    const search = document.querySelector('#search');
    const links = [...document.querySelectorAll('[data-topic]')];
    const groups = [...document.querySelectorAll('.sidebar details')];
    const empty = document.querySelector('.no-results');
    let previousStates = null;
    if (innerWidth <= 700) search.placeholder = 'Search...';
    search.addEventListener('input', () => {
        const term = search.value.trim().toLowerCase();
        if (term && previousStates === null) previousStates = groups.map(group => group.open);
        let count = 0;
        links.forEach(link => {
            link.hidden = Boolean(term) && !(link.textContent + ' ' + (texts[link.dataset.topic] || '')).toLowerCase().includes(term);
            if (!link.hidden) count++;
        });
        groups.forEach((group, index) => {
            group.hidden = ![...group.querySelectorAll('a')].some(link => !link.hidden);
            if (term) group.open = true;
            else if (previousStates) group.open = previousStates[index];
        });
        if (!term) previousStates = null;
        empty.hidden = count > 0;
        if (term && innerWidth <= 700) setMenu(true);
    });
    document.addEventListener('keydown', event => {
        if (event.key === '/' && !event.ctrlKey && !event.metaKey && !event.altKey && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName) && !document.activeElement.isContentEditable) {
            event.preventDefault();
            search.focus();
        }
        if (event.key === 'Escape') {
            search.value = '';
            search.dispatchEvent(new Event('input'));
            if (document.body.classList.contains('nav-open')) menu.focus();
            setMenu(false);
        }
    });
    const headings = [...document.querySelectorAll('#doc-content h2[id]')];
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                document.querySelectorAll('.toc nav a').forEach(link => {
                    if (link.hash === '#' + entry.target.id) link.setAttribute('aria-current', 'location');
                    else link.removeAttribute('aria-current');
                });
            });
        }, {rootMargin: '-100px 0px -60% 0px'});
        headings.forEach(heading => observer.observe(heading));
    }
}
icons();

const canvas = document.querySelector('#threads');
if (canvas) {
    const context = canvas.getContext('2d');
    const draw = () => {
        if (!context) return;
        const ratio = Math.min(devicePixelRatio, 2);
        const width = canvas.clientWidth, height = canvas.clientHeight;
        canvas.width = width * ratio;
        canvas.height = height * ratio;
        context.scale(ratio, ratio);
        for (let i = 0; i < 24; i++) {
            const x = width * .60 + i * 26;
            context.beginPath();
            context.strokeStyle = i % 5 === 0 ? '#659f7045' : '#659f7020';
            context.lineWidth = 1;
            context.moveTo(x, -80);
            context.bezierCurveTo(x - 220, height * .35, x + 300, height * .47, x - 20, height + 100);
            context.stroke();
        }
    };
    draw();
    addEventListener('resize', draw);
}
