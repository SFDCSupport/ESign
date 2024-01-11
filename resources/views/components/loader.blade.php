<div id="loader" style="display: none;">
    <div class="bouncing-bar">
        <div class="line"></div>
        <div class="line"></div>
        <div class="line"></div>
        <div class="line"></div>
    </div>
</div>

<script>
    const loader = (function () {
        const ele = function () {
            return $('#loader');
        };

        return {
            show: function () {
                ele().show();
            },
            hide: function () {
                ele().hide();
            },
            toggle: function () {
                this.isHidden() ? ele().show() : ele().hide();
            },
            isHidden: function () {
                return !this.isVisible();
            },
            isVisible: function () {
                return ele().is(':visible');
            }
        };
    })();

    $(function () {
        $(document).on('loader:show', loader.show);
        $(document).on('loader:hide', loader.hide);
        $(document).on('loader:toggle', loader.toggle);
    });
</script>
