export const Paginator = (taxonomy) => ({
    loading: true,
    current: 1,
    selected: {},
    data: [],
    selectedstring: '',
    selectedCount: 0,
    total: 0,
    pages: 0,
    taxonomy,
    objects: 0,
    formattedTotal: 0,
    searchTerm: null,
    searchText: '',
    initialised: false,
    async page(page) {
        this.loading = true;

        const { rest, nonce } = ag_admin;

        const data = new FormData;
        data.append('taxonomy', this.taxonomy);
        data.append('page', page);
        data.append('t', Date.now());

        if (this.searchTerm) {
            data.append('search', this.searchTerm);
        }

        const q = new URLSearchParams(data).toString();

        const response = await (
            await (
                fetch(
                    `${rest}?${q}`,
                    {
                        headers: {
                            'X-WP-Nonce': nonce,
                        }
                    }

                )
            )
        ).json();

        const {
            data: terms,
            page: current,
            total,
            pages,
            objects,
        } = response;

        this.objects = objects;
        this.data = terms;
        this.current = current;
        this.total = total;
        this.pages = pages;

        this.formattedTotal = Intl.NumberFormat().format(objects * total);

        this.loading = false;
        this.initialised = true;
    },
    async init() {

        this.$watch('selected', value => console.log(value));

        this.$watch('selected', (value) => {
            this.selectedstring = JSON.stringify(Object.fromEntries(Object.entries(value).filter(([, v]) => v)));
            this.selectedCount = Object.keys(Object.fromEntries(Object.entries(value).filter(([, v]) => v))).length;
        });

        const { rest, nonce } = ag_admin;
        const data = new FormData;
        data.append('taxonomy', this.taxonomy);
        data.append('t', Date.now());

        const q = new URLSearchParams(data).toString();

        try {
            const selected = await (
                await (
                    fetch(
                        `${rest}/selected?${q}`,
                        {
                            headers: {
                                'X-WP-Nonce': nonce,
                            }
                        }
                    )
                )
            ).json();

            this.selected = Object.keys(selected).length ? selected : {};
        } catch (error) {
            const { load_error } = ag_content_params;

            document.querySelector('.wrap h2').insertAdjacentHTML('afterend', `<div class="notice notice-error"><p><strong>${load_error}</strong></p></div>`);

            window.scrollTo({
                top: 0,
                left: 0,
                behavior: "smooth",
            })
            this.selected = {};
        }


        this.$watch('searchTerm', (value) => this.page(1));

        this.page(1);
    },
    async all() {
        if (Object.keys(this.selected).length) {
            this.selected = {};
            return;
        }

        this.loading = true;

        const { rest, nonce } = ag_admin;
        const data = new FormData;
        data.append('taxonomy', this.taxonomy);
        data.append('t', Date.now());

        if (this.searchTerm) {
            data.append('search', this.searchTerm);
        }

        const q = new URLSearchParams(data).toString();

        const selected = await(
            await(
                fetch(
                    `${rest}/select-all?${q}`,
                    {
                        headers: {
                            'X-WP-Nonce': nonce,
                        }
                    }
                )
            )
        ).json();

        this.selected = Object.keys(selected).length ? selected : {};

        this.loading = false;
    },
    search(term) {
        this.searchTerm = term;

        if (!term) {
            this.searchText = '';
        }
    }

})
