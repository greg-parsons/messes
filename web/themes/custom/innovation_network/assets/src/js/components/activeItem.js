/**
 * Compare path of links that are children of the supplied parent
 * selector to current path and add an `active` class if it matches.
 *
 * @param {string} parent Parent selector
 */
export default function activeItem(parent) {
  const currentUrl = new URL(window.location.href);
  const links = document.querySelectorAll(`${parent} a`);

  links.forEach(link => {
    const linkUrl = new URL(link.href);

    if (linkUrl.pathname === currentUrl.pathname) {

        /**
           The by type sidebar on the events block needs to highlight when the type 
           parameter in the URL matches. Other pages with query parameter options,
           only the base URL needs to match.
        */
        if(jQuery(parent).hasClass('sidebar__block--views_block__events_type_terms_block_1')) {
            if(linkUrl.searchParams.has('type') 
                && currentUrl.searchParams.has('type')
                && linkUrl.searchParams.get('type') == currentUrl.searchParams.get('type') 
            ) {
                link.classList.add('active');
            }
        } else {
            link.classList.add('active');
        }
    }
  });
}
