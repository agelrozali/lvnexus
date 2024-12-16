const storeItems = async (items, nonce = null, idx = 0) => {
    if (!items.length) {
        return true;
    }

    console.log(idx);

    const { ajaxurl } = window;

    const data = new FormData;

    data.append('action', 'age_gate_store_terms');
    data.append('idx', idx);

    for (const [key, value] of Object.entries(items[0])) {
        data.append(`ag_settings[${key}]`, value);
    }

    try {
        const response = await(
            await (
                fetch (
                    ajaxurl,
                    {
                        method: 'POST',
                        body: data,
                    }
                )
            )
        ).json();
    } catch (e) {
        return false;
    }

    items.shift();

    const prog = document.querySelector('.age-gate-progress');

    if (prog) {
        prog.value = idx + 1;
    }

    return storeItems(items, nonce, idx + 1);
}


window.addEventListener('DOMContentLoaded', () => {
    const contentForm = document.querySelector('[data-form="content"]');

    if (contentForm) {
        contentForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const fields = Array.from(document.querySelectorAll('[name="ag_settings[terms][]"]'));

            let all = [];
            const backup = [];

            fields.forEach((field) => {
                const object = JSON.parse(field.value);
                backup.push(field.value);

                var values = Object.values(object);
                var portion = {};

                var final = [];
                var counter = 0;

                for (var key in object) {
                    if (counter !== 0 && counter % 50 === 0) {
                        final.push(portion);
                        portion = {};
                    }
                    portion[key] = values[counter];
                    counter++
                }
                final.push(portion);

                field.value = '';

                all = all.concat(final);
            })


            const progress = document.createElement('progress');
            const wrapper = document.createElement('div');
            wrapper.className = 'age-gate-overlay';
            progress.className = 'age-gate-progress';
            wrapper.append(progress);
            document.body.appendChild(wrapper);
            progress.max = all.length;
            progress.value = 0;

            // final.forEach(wl => console.log(wl));

            const r = await storeItems(all);

            if (r === false) {
                backup.forEach((d, i) => fields[i].value = d);

                const overlay = document.querySelector('.age-gate-overlay');

                if (overlay) {
                    overlay.parentNode.removeChild(overlay)
                }

                const { save_error } = ag_content_params;

                document.querySelector('.wrap h2').insertAdjacentHTML('afterend', `<div class="notice notice-error"><p><strong>${save_error}</strong></p></div>`);

                window.scrollTo({
                    top: 0,
                    left: 0,
                    behavior: "smooth",
                })
                return;
            }

            contentForm.submit();

            // console.log(r);
        })
    }
});
