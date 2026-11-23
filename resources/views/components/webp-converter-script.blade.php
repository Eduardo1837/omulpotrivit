<script>
    window.convertImageToWebp = (file, quality = 0.85, maxWidth = 1920) => {
        if (!file || file.type === 'image/webp') {
            return Promise.resolve(file);
        }

        return new Promise((resolve) => {
            const objectUrl = URL.createObjectURL(file);
            const img = new Image();

            img.onload = () => {
                URL.revokeObjectURL(objectUrl);

                let width = img.naturalWidth || img.width;
                let height = img.naturalHeight || img.height;
                if (maxWidth && width > maxWidth) {
                    height = Math.round((height * maxWidth) / width);
                    width = maxWidth;
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                if (!ctx) {
                    resolve(file);
                    return;
                }

                ctx.drawImage(img, 0, 0, width, height);
                canvas.toBlob(
                    (blob) => {
                        if (!blob) {
                            resolve(file);
                            return;
                        }
                        const baseName = file.name.replace(/\.[^.]+$/, '') || 'image';
                        resolve(new File([blob], `${baseName}.webp`, { type: 'image/webp' }));
                    },
                    'image/webp',
                    quality
                );
            };

            img.onerror = () => {
                URL.revokeObjectURL(objectUrl);
                resolve(file);
            };

            img.src = objectUrl;
        });
    };
</script>
