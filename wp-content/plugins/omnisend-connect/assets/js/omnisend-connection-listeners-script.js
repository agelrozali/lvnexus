(function(){
    const omnisendConnectActions = document.querySelectorAll('.omnisend-connect-action');
    let interval;

    function waitUntilAPIKeyAndRefresh() {
        if (interval) {
            return
        }

        interval = setInterval(async () => {
            fetch(`<?php echo esc_url( home_url( '/wp-json/omnisend-api/v1/connected' ) ); ?>?=${Date.now()}`).then(r => r.json()).then(connected => {
                if (!connected) {
                    return
                }
                clearInterval(interval);
                try {
                    omnisendConnectActions.forEach((element) => {
                        element.removeEventListener('click', waitUntilAPIKeyAndRefresh);
                    });
                } finally {
                    location.reload();
                }
            })
        }, 1000);
    }

    omnisendConnectActions.forEach((element) => {
        element.addEventListener('click', waitUntilAPIKeyAndRefresh);
    });
})();