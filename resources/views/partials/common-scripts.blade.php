<script>
    const loadedData = [
        {
            on_page: 1,
            signer_id: 1,
            type: 'signature_pad',
            offset_x: 238.34674585238713,
            offset_y: 112.34266801044906,
            width: 184.46467700999992,
            height: 37.25560053999998,
            signer_index: '1',
        },
        {
            on_page: 1,
            signer_id: 2,
            type: 'signature_pad',
            offset_x: 253.15437142614985,
            offset_y: 218.27301736782994,
            width: 126.44699999999999,
            height: 25.537999999999993,
            signer_index: '1',
        },
        {
            on_page: 1,
            signer_id: 3,
            type: 'email',
            offset_x: 72,
            offset_y: 28,
            width: 47.2,
            height: 22.599999999999998,
            signer_index: '3',
        },
        {
            on_page: 1,
            signer_id: 4,
            type: 'textarea',
            offset_x: 375,
            offset_y: 20,
            width: 68.3,
            height: 22.599999999999998,
            signer_index: '3',
            text: 'hello anand',
        },
        {
            on_page: 2,
            signer_id: 5,
            type: 'text',
            offset_x: 185.7008572647063,
            offset_y: 50.38610868284016,
            width: 33.95649999999999,
            height: 25.537999999999993,
            position: '1',
        },
    ];

    const getActiveSignerIndex = () =>
        $('.selectedSigner[data-active-signer-index]').attr(
            'data-active-signer-index',
        );
    const generateUniqueId = (() => {
        let counter = 0;

        return () => {
            const timestamp = new Date().getTime();
            return `${timestamp}-${counter++}`;
        };
    })();
    const getCSRFToken = () =>
        $('meta[name="csrf-token"]').attr('content') || null;
    const getDocumentId = () =>
        $('meta[name="document-id"]').attr('content') || null;
    const getSignerId = () =>
        $('meta[name="signer-id"]').attr('content') || null;
    const blank = (value) => {
        if (Array.isArray(value)) {
            return value.length === 0;
        }

        if (value instanceof Date) {
            return false;
        }

        if (typeof value === 'object' && value !== null) {
            return Object.keys(value).length === 0;
        }

        return ['', null, undefined].includes(value);
    };
    const dataURLtoBlob = (dataurl) => {
        let arr = dataurl.split(','),
            mime = arr[0].match(/:(.*?);/)[1],
            bstr = atob(arr[1]),
            n = bstr.length,
            u8arr = new Uint8Array(n);
        while (n--) {
            u8arr[n] = bstr.charCodeAt(n);
        }
        return new Blob([u8arr], { type: mime });
    };
    const svgToDataUrl = (svg) => 'data:image/svg+xml;base64,' + btoa(svg);
    const trimSvgWhitespace = (svgContent) => {
        if (!svgContent) {
            return null;
        }

        let svgContainer = document.createElement('div');
        svgContainer.classList.add('invisible');
        svgContainer.classList.add('position-absolute');
        svgContainer.classList.add('top-0');
        svgContainer.classList.add('start-0');
        svgContainer.style = 'z-index: -1;';
        svgContainer.innerHTML = svgContent;

        document.body.appendChild(svgContainer);

        let svg = svgContainer.querySelector('svg');
        let box = svg.getBBox();

        svg.setAttribute(
            'viewBox',
            [box.x, box.y, box.width, box.height].join(' '),
        );

        document.body.removeChild(svgContainer);

        return svgContainer.innerHTML;
    };
    const ordinal = (number) => {
        let suffix;

        if (number % 100 >= 11 && number % 100 <= 13) {
            suffix = '{{ __('esign::label.th') }}';
        } else {
            switch (number % 10) {
                case 1:
                    suffix = '{{ __('esign::label.st') }}';
                    break;
                case 2:
                    suffix = '{{ __('esign::label.nd') }}';
                    break;
                case 3:
                    suffix = '{{ __('esign::label.rd') }}';
                    break;
                default:
                    suffix = '{{ __('esign::label.th') }}';
                    break;
            }
        }

        return number + suffix;
    };

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': getCSRFToken(),
        },
    });

    $.fn.highestData = function (data = 'signer') {
        let highest = -Infinity;

        this.each(function () {
            const value = parseInt($(this).attr('data-' + data), 10);

            if (!isNaN(value)) {
                highest = Math.max(highest, value);
            }
        });

        return isFinite(highest) ? highest : null;
    };
</script>
