import Alpine from 'alpinejs';
import './echo';

window.Alpine = Alpine;

Alpine.data('searchableSelect', (config = {}) => ({
    open: false,
    query: '',
    label: '',
    highlight: -1,
    typing: false,
    options: [],
    placeholder: config.placeholder || '-- Pilih --',
    required: config.required || false,
    drop: { top: 0, left: 0, width: 0 },
    _onDocClick: null,
    _onScroll: null,
    _raf: null,
    _openedUp: null,

    get input() { return this.$refs.input; },
    get sel() { return this.$refs.select; },

    init() {
        this.scan();
        this.$nextTick(() => {
            const preset = this.sel.value;
            if (preset) {
                const m = this.options.find(o => String(o.value) === String(preset));
                if (m) { this.label = m.label; this.query = m.label; }
            }
        });
        this._onDocClick = (e) => {
            if (!this.$root.contains(e.target)) this.open = false;
        };
        this._onScroll = () => {
            if (!this.open || this._raf) return;
            this._raf = requestAnimationFrame(() => {
                this._raf = null;
                if (this.open) this.reposition();
            });
        };
        document.addEventListener('click', this._onDocClick);
        document.addEventListener('scroll', this._onScroll, true);
        window.addEventListener('resize', this._onScroll);
        this.$watch('open', (val) => {
            if (val) {
                this.$nextTick(() => { this.reposition(); });
                setTimeout(() => { if (this.open) this.reposition(); }, 220);
            }
        });
    },

    destroy() {
        document.removeEventListener('click', this._onDocClick);
        document.removeEventListener('scroll', this._onScroll, true);
        window.removeEventListener('resize', this._onScroll);
        if (this._raf) { cancelAnimationFrame(this._raf); this._raf = null; }
    },

    scan() {
        if (!this.sel) return;
        this.options = Array.from(this.sel.options)
            .map(o => ({
                value: o.value,
                label: (o.textContent || '').replace(/\s+/g, ' ').trim(),
                data: Array.from(o.attributes)
                    .filter(a => a.name.startsWith('data-'))
                    .reduce((acc, a) => { acc[a.name] = a.value; return acc; }, {}),
            }))
            .filter(o => o.label !== '' && !o.hidden && (!this.required || o.value !== ''));
    },

    get filtered() {
        if (!this.typing || !this.query) return this.options;
        const q = this.query.toLowerCase().trim();
        return this.options.filter(o =>
            (o.label + ' ' + Object.values(o.data).join(' ')).toLowerCase().includes(q)
        );
    },

    reposition() {
        if (!this.input) return;
        const r = this.input.getBoundingClientRect();
        if (!r.width || !r.height) return;
        const below = window.innerHeight - r.bottom;
        const above = r.top;
        if (this._openedUp === null) this._openedUp = below < 200 && above > 200;
        this.drop = {
            top: Math.round(this._openedUp ? r.top - 8 : r.bottom + 6),
            left: Math.round(Math.min(r.left, Math.max(8, window.innerWidth - 300))),
            width: Math.max(180, Math.round(r.width)),
        };
    },

    onFocus() {
        this.typing = false;
        this.open = true;
        this.highlight = -1;
        this.scan();
        if (this.sel.value) {
            const cur = this.options.find(o => String(o.value) === String(this.sel.value));
            if (cur) { this.label = cur.label; this.query = cur.label; }
        }
        this._openedUp = null;
        this.$nextTick(() => { this.reposition(); });
        if (this.query === this.label) {
            this.$nextTick(() => this.input.select());
        }
    },

    onBlur() {
        setTimeout(() => {
            this.open = false;
            this.typing = false;
            this.query = this.label;
        }, 120);
    },

    onInput() {
        this.typing = true;
        this.highlight = -1;
        this.$nextTick(() => { this.reposition(); });
    },

    onKeydown(e) {
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            if (!this.open) this.onFocus();
            if (e.key === 'ArrowDown') {
                this.highlight = Math.min(this.highlight + 1, this.filtered.length - 1);
            } else {
                this.highlight = Math.max(this.highlight - 1, -1);
            }
        } else if (e.key === 'Enter') {
            const list = this.filtered;
            if (this.open && this.highlight > -1 && list[this.highlight]) {
                e.preventDefault();
                this.select(list[this.highlight]);
            } else if (this.typing && list.length === 1) {
                e.preventDefault();
                this.select(list[0]);
            } else if (this.typing) {
                e.preventDefault();
            }
        } else if (e.key === 'Escape') {
            this.open = false;
            this.query = this.label;
        } else if (e.key === 'Tab') {
            this.open = false;
        }
    },

    select(o) {
        if (!o) return;
        this.sel.value = o.value;
        this.label = o.label;
        this.query = o.label;
        this.typing = false;
        this.open = false;
        this.highlight = -1;
        this.sel.dispatchEvent(new Event('change', { bubbles: true }));
    },

    clear() {
        this.sel.value = '';
        this.label = '';
        this.query = '';
        this.typing = false;
        this.open = true;
        this.highlight = -1;
        this._openedUp = null;
        this.sel.dispatchEvent(new Event('change', { bubbles: true }));
        this.$nextTick(() => {
            this.reposition();
            this.input.focus();
        });
    },
}));

Alpine.start();
