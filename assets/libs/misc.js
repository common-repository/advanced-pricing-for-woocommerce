export function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

export function clipboard() {
    let popOvers = jQuery('[data-bs-toggle="popover"]');
    if ( popOvers.length ) {
        new bootstrap.Popover( '[data-bs-toggle="popover"]' );
    }
    jQuery(document).on( 'click', '.gpls-general-clipboard-icon-btn', function(e) {
        e.preventDefault();
        let btn = jQuery(this);
        let target = btn.data('target');
        let text   = jQuery( target ).text();
        navigator.clipboard.writeText( text );

        setTimeout(() => {
            let popover = bootstrap.Popover.getOrCreateInstance( btn );
            popover.hide();
        }, 1000 );
    });
}

export function collapseInit() {
    const collapseElementList = jQuery('[data-bs-element="collapse"]');
    collapseElementList.each(
        ( index, collapseEl ) => {
            collapseEl = jQuery(collapseEl);
            new bootstrap.Collapse(collapseEl);
            let defaultVal = collapseEl.data('bs-default');
            if ( defaultVal === 'show' ) {
                collapseEl.toggle('show');
            } else {
                collapseEl.toggle('hide');
            }
        }
    );
}


export function tooltipInit() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    });
}
