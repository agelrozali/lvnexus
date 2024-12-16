import vhCheck from 'vh-check';

const test = vhCheck({
    cssVarName: 'ag-vh-offset'
});

window.addEventListener('age_gate_shown', () => {

    if (!navigator.cookieEnabled) {
        const {
            cookies,
        } = age_gate_common;

        document.querySelector('.age-gate-form, .age-gate__form').insertAdjacentHTML('afterbegin', `<p class="age-gate__error">${cookies}</p>`);

    }

});
