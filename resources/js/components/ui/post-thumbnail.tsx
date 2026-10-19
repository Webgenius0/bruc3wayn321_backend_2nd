import React, { useState, useEffect } from 'react';

interface PostThumbnailProps {
    src?: string | string[] | null;
    alt?: string;
    width?: number | string;
    height?: number | string;
    className?: string;
    style?: React.CSSProperties;
    rounded?: string;
}

export function formatImageUrl(src?: string | string[] | null): string | null {
    if (!src) return null;

    let target = src;
    if (Array.isArray(src)) {
        if (src.length === 0) return null;
        target = src[0];
    }

    if (typeof target !== 'string') return null;
    const clean = target.trim();
    if (!clean || clean === 'null' || clean === 'undefined') return null;

    if (
        clean.startsWith('http://') ||
        clean.startsWith('https://') ||
        clean.startsWith('data:') ||
        clean.startsWith('blob:')
    ) {
        return clean;
    }

    if (clean.startsWith('/')) {
        return clean;
    }

    return `/${clean}`;
}

export function PostThumbnail({
    src,
    alt = 'Post thumbnail',
    width = 60,
    height = 60,
    className = '',
    style = {},
    rounded = '6px',
}: PostThumbnailProps) {
    const [imageError, setImageError] = useState(false);
    const imageUrl = formatImageUrl(src);

    useEffect(() => {
        setImageError(false);
    }, [src]);

    const containerStyle: React.CSSProperties = {
        width: typeof width === 'number' ? `${width}px` : width,
        height: typeof height === 'number' ? `${height}px` : height,
        borderRadius: rounded,
        overflow: 'hidden',
        ...style,
    };

    if (imageUrl && !imageError) {
        return (
            <img
                src={imageUrl}
                alt={alt}
                className={`object-fit-cover flex-shrink-0 ${className}`}
                onError={() => setImageError(true)}
                style={containerStyle}
            />
        );
    }

    return (
        <div
            className={`d-flex flex-column align-items-center justify-content-center text-muted flex-shrink-0 border ${className}`}
            style={{
                ...containerStyle,
                backgroundColor: 'rgba(148, 163, 184, 0.12)',
                borderColor: 'rgba(148, 163, 184, 0.2)',
            }}
            title="No image available"
        >
            <i
                className="material-symbols-outlined opacity-50"
                style={{ fontSize: typeof height === 'number' ? Math.max(16, Math.round(height * 0.4)) : 22 }}
            >
                image
            </i>
        </div>
    );
}

export default PostThumbnail;
