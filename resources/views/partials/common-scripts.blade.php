<script>
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

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': getCSRFToken(),
        },
    });
</script>
