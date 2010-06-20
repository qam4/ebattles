/**
 * The "nova" theme markup for Shadowbox.
 *
 * This file is part of Shadowbox.
 *
 * @author      FrosT ]S[tudio Design - "Infernal" <FrosT@frost-haker.com>
 * @copyright   2000-2008 FrosT ][orporation
 * @license     http://www.frost-haker.com
 * @version     V.1.0.1 $
 */

if(typeof Shadowbox == 'undefined'){
    throw 'Unable to load Shadowbox skin, base library not found.';
}

/**
 * The HTML markup to use for Shadowbox.
 *
 * IMPORTANT: The script depends on most of these elements being present.
 *
 * @property    {Object}    SKIN
 * @public
 * @static
 */
Shadowbox.SKIN = {

    markup:     '<div id="shadowbox_container">' +
                    '<div id="shadowbox_overlay"></div>' +
                    '<div id="shadowbox">' +
                        '<div id="shadowbox_title">' +
                            '<div id="shadowbox_title_inner"></div>' +
                        '</div>' +
                        '<div id="shadowbox_body">' +
                            '<div id="shadowbox_body_inner"></div>' +
                            '<div id="shadowbox_loading">' +
                                '<div id="shadowbox_loading_indicator"></div>' +
                                '<span><a onclick="Shadowbox.close();">{cancel}</a></span>' +
                            '</div>' +
                        '</div>' +
                        '<div id="shadowbox_info">' +
                            '<div id="shadowbox_info_inner">' +
                                '<div id="shadowbox_counter"></div>' +
                                '<div id="shadowbox_nav">' +
                                    '<a id="shadowbox_nav_close" title="{close}" onclick="Shadowbox.close()"></a>' +
                                    '<a id="shadowbox_nav_next" title="{next}" onclick="Shadowbox.next()"></a>' +
                                    '<a id="shadowbox_nav_play" title="{play}" onclick="Shadowbox.play()"></a>' +
                                    '<a id="shadowbox_nav_pause" title="{pause}" onclick="Shadowbox.pause()"></a>' +
                                    '<a id="shadowbox_nav_previous" title="{previous}" onclick="Shadowbox.previous()"></a>' +
                                '</div>' +
                                '<div class="shadowbox_clear"></div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>',

    png_fix:    [
        'shadowbox_nav_close',
        'shadowbox_nav_next',
        'shadowbox_nav_play',
        'shadowbox_nav_pause',
        'shadowbox_nav_previous'
    ]

};
