import React, { useState } from 'react';

interface UserAvatarProps {
    src?: string | null;
    name?: string | null;
    alt?: string;
    size?: number;
    className?: string;
    style?: React.CSSProperties;
    showStatus?: boolean;
    statusColor?: string;
}

const BG_COLORS = [
    '#4f46e5', // Indigo
    '#0284c7', // Sky
    '#059669', // Emerald
    '#d97706', // Amber
    '#7c3aed', // Violet
    '#db2777', // Pink
    '#2563eb', // Blue
    '#0d9488', // Teal
];

function getInitials(name?: string | null): string {
    if (!name) return 'U';
    const parts = name.trim().split(/\s+/).filter(Boolean);
    if (parts.length === 0) return 'U';
    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

function getColorForName(name?: string | null): string {
    if (!name) return BG_COLORS[0];
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    const index = Math.abs(hash) % BG_COLORS.length;
    return BG_COLORS[index];
}

export function formatAvatarUrl(src?: string | null): string | null {
    if (!src || src.trim() === '') return null;
    const clean = src.trim();

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

export function UserAvatar({
    src,
    name,
    alt = 'User avatar',
    size = 40,
    className = '',
    style = {},
    showStatus = false,
    statusColor = '#22c55e',
}: UserAvatarProps) {
    const [imageError, setImageError] = useState(false);
    const avatarUrl = formatAvatarUrl(src);

    const initials = getInitials(name);
    const bgColor = getColorForName(name);
    const fontSize = Math.max(11, Math.round(size * 0.38));

    return (
        <div
            className={`position-relative d-inline-flex flex-shrink-0 align-items-center justify-content-center ${className}`}
            style={{
                width: `${size}px`,
                height: `${size}px`,
                ...style,
            }}
        >
            {avatarUrl && !imageError ? (
                <img
                    src={avatarUrl}
                    alt={alt}
                    className="rounded-circle w-100 h-100 object-fit-cover"
                    onError={() => setImageError(true)}
                    style={{
                        width: `${size}px`,
                        height: `${size}px`,
                        objectFit: 'cover',
                    }}
                />
            ) : (
                <div
                    className="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold user-select-none shadow-sm"
                    style={{
                        width: `${size}px`,
                        height: `${size}px`,
                        backgroundColor: bgColor,
                        fontSize: `${fontSize}px`,
                        letterSpacing: '0.5px',
                    }}
                    title={name || 'User'}
                >
                    {name ? (
                        initials
                    ) : (
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="2"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            style={{ width: `${size * 0.55}px`, height: `${size * 0.55}px` }}
                        >
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    )}
                </div>
            )}

            {showStatus && (
                <span
                    className="border border-2 border-white rounded-circle position-absolute"
                    style={{
                        width: `${Math.max(8, Math.round(size * 0.28))}px`,
                        height: `${Math.max(8, Math.round(size * 0.28))}px`,
                        backgroundColor: statusColor,
                        bottom: 0,
                        right: 0,
                    }}
                />
            )}
        </div>
    );
}

export default UserAvatar;
