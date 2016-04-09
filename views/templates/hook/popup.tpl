{if $pe && (!$pe.options->DiscountCountdown || ($pe.options->DiscountCountdown && !isset($dc) && !$dc)) }
    {if $pe.options->stickerActive}
        {$pestickerparam="pesticker{$pe.id_popupeverywhere}"}
        <div style="{if $pe.options->stickerPadding}padding:{$pe.options->stickerPadding|escape:'html':'UTF-8'};{/if}{if $pe.options->stickerMargin}margin:{$pe.options->stickerMargin|escape:'html':'UTF-8'};{/if}{if $pe.options->stickerPosition}{$pe.options->stickerPosition|escape:'html':'UTF-8'};{/if}{if $pe.options->backgroundColorSticker}background-color:{$pe.options->backgroundColorSticker|escape:'html':'UTF-8'};{/if}{if $pe.options->borderColorSticker}border-color:{$pe.options->borderColorSticker|escape:'html':'UTF-8'};{/if}{if $pe.options->borderWidthSticker}border-width:{$pe.options->borderWidthSticker|escape:'html':'UTF-8'};{/if}{if $pe.options->borderStyleSticker}border-style:{$pe.options->borderStyleSticker|escape:'html':'UTF-8'};{/if}{if $pe.options->styleSticker}{$pe.options->styleSticker|escape:'html':'UTF-8'};{/if}{if !isset($smarty['cookies'][$pestickerparam]) && $pe.options->stickerDisplay=='after'}display:none; {/if}" class="pe-sticker">
            <a href="#">
                {html_entity_decode($pe.sticker|escape:'htmlall':'UTF-8')}
            </a>
        </div>
    {/if}

    <div id="ouibounce-modal">
        <div class="underlay"></div>
        <div class="modal" style="{if $pe.options->backgroundColor}background-color:{$pe.options->backgroundColor|escape:'html':'UTF-8'};{/if}{if $pe.options->borderColor}border-color:{$pe.options->borderColor|escape:'html':'UTF-8'};{/if}{if $pe.options->borderWidth}border-width:{$pe.options->borderWidth|escape:'html':'UTF-8'};{/if}{if $pe.options->borderStyle}border-style:{$pe.options->borderStyle|escape:'html':'UTF-8'};{/if}{if $pe.options->style}{$pe.options->style|escape:'html':'UTF-8'}{/if}">

            {if $pe.header}
                <div class="modal-title" style="{if $pe.options->backgroundColorHeader}background-color:{$pe.options->backgroundColorHeader|escape:'html':'UTF-8'};{/if}">
                    <h3 style="{if $pe.options->colorHeader}color:{$pe.options->colorHeader|escape:'html':'UTF-8'};{/if}">{$pe.header|escape:'html':'UTF-8'}</h3>
                </div>
            {/if}
            <div class="modal-body">
                {html_entity_decode($pe.content|escape:'htmlall':'UTF-8')}
                {html_entity_decode($pe.html|escape:'UTF-8')}
                {if $pe.button}
                    <p class="text-center">
                        <a style="{if $pe.options->backgroundColorButton}background-color:{$pe.options->backgroundColorButton|escape:'html':'UTF-8'};{/if}{if $pe.options->colorButton}color:{$pe.options->colorButton|escape:'html':'UTF-8'};{/if}" href="{$pe.link|escape:'html':'UTF-8'}" class="btn-modal">{$pe.button|escape:'html':'UTF-8'}</a>
                    </p>
                {/if}
            </div>
            {if $pe.close}
                <div class="modal-footer">
                    <p>{$pe.close|escape:'html':'UTF-8'}</p>
                </div>
            {/if}
        </div>
    </div>
    <script type="text/javascript">
        var _ouibounce = ouibounce(document.getElementById('ouibounce-modal'), {
        {if $pe.options->Sensitivity}
        sensitivity: {$pe.options->Sensitivity},
        {/if}
        {if $pe.options->Aggressive}
        aggressive: true,
        {/if}
        {if $pe.options->Timer}
        timer: {$pe.options->Timer},
        {/if}
        {if $pe.options->Delay}
        delay: {$pe.options->Timer},
        {/if}
        {if $pe.options->CookieExpiration|escape:'html':'UTF-8'}
        cookieExpire : '{$pe.options->CookieExpiration|escape:'html':'UTF-8'}',
        {/if}
        sitewide: true,
                cookieName : 'pe{$pe.id_popupeverywhere|escape:'html':'UTF-8'}',
                callback: function () {
                closeModal();
                        $.cookie('pesticker{$pe.id_popupeverywhere|escape:'html':'UTF-8'}', {$pe.id_popupeverywhere|escape:'html':'UTF-8'}, { expires: {$pe.options->CookieExpiration|escape:'html':'UTF-8'}, path: '/' });
        {if $pe.options->GoogleAnalytics}
                ga('send', 'event', 'PopupEverywhere', 'OpenWindow', '{$pe.alias|default|escape:'htmlall':'UTF - 8'}');
        {/if}
                }
        });
                function showStickerDiscount() {
                $('.pe-sticker').slideDown('slow');
                }
        function closeModal() {
        $('body').on('click', function () {
        showStickerDiscount();
                $('#ouibounce-modal').hide();
        });
                $('#ouibounce-modal .modal-footer').on('click', function () {
        showStickerDiscount();
                $('#ouibounce-modal').hide();
        });
        }

        $('#ouibounce-modal .modal').on('click', function (e) {
        e.stopPropagation();
        });
                $('.pe-sticker a').click(function (e) {
        {if $pe.options->GoogleAnalytics}
        ga('send', 'event', 'PopupEverywhere', 'ClickSticker', '{$pe.alias|default|escape:'htmlall':'UTF - 8'}');
        {/if}
        _ouibounce.afire();
                return false;
        })

        {if $pe.options->AutoPopup}
        var autopopupvar = 'peauto{$pe.id_popupeverywhere|escape:'html':'UTF-8'}';
                var popupdelay;
                var date = new Date();
                if (!$.cookie(autopopupvar)){
        popupdelay = date.getTime() + ({$pe.options->AutoPopup} * 1000);
                $.cookie(autopopupvar, popupdelay);
        } else{
        popupdelay = $.cookie(autopopupvar);
        }
        if (popupdelay != 1){
        setTimeout(function () {
        _ouibounce.fire();
                $.cookie(autopopupvar, 1);
        }, (popupdelay - date.getTime()));
        }
        {/if}

    </script>
{/if}
