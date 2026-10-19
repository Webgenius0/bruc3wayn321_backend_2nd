import { Head } from "@inertiajs/react";
import { PostThumbnail } from "@/components/ui/post-thumbnail";

interface user {
    id: number;
    first_name: string;
    last_name: string;
    email?: string;
    avatar?: string;
}

interface Post {
    id: number;
    slug: string;
    content: string;
    location: string;
    images: string[];
    audios: string[];
    videos: string[];
    likes_count: number;
    comments_count: number;
    views_count: number;
    created_at: string;
    user: user;
}

interface Props {
    post: Post;
}

export default function Show({ post }: Props) {
    return (
        <>
            <Head title="Post Details" />

            <div
                className="main-content-container overflow-hidden"
                style={{ minHeight: "75vh" }}
            >
                <div className="row">
                    <div className="col-lg-12">

                        <div className="d-flex justify-content-between align-items-center mb-4">
                            <button
                                onClick={() => window.history.back()}
                                className="btn btn-secondary text-white"
                            >
                                Back
                            </button>
                        </div>

                        <div className="card border-0 bg-white rounded-10">
                            <div className="card-body p-4">

                                {/* Header */}
                                <div className="mb-4">
                                    <h4 className="mb-2">
                                        Post #{post.id}
                                    </h4>

                                    <p className="text-muted mb-1">
                                        Published by:
                                        <strong className="ms-2">
                                            {post.user?.first_name ? post.user?.first_name + " " + post.user?.last_name : "Unknown"}
                                        </strong>
                                    </p>

                                    <p className="text-muted mb-0">
                                        {post.created_at}
                                    </p>
                                </div>

                                {/* Stats */}
                                <div className="row mb-4">
                                    <div className="col-md-4">
                                        <div className="border rounded p-3 text-center">
                                            <h5>{post.likes_count ?? 0}</h5>
                                            <span>Likes</span>
                                        </div>
                                    </div>

                                    <div className="col-md-4">
                                        <div className="border rounded p-3 text-center">
                                            <h5>{post.comments_count ?? 0}</h5>
                                            <span>Comments</span>
                                        </div>
                                    </div>

                                    <div className="col-md-4">
                                        <div className="border rounded p-3 text-center">
                                            <h5>{post.views_count ?? 0}</h5>
                                            <span>Views</span>
                                        </div>
                                    </div>
                                </div>

                                {/* Location */}
                                {post.location && (
                                    <div className="mb-4">
                                        <h6>Location</h6>
                                        <p>{post.location}</p>
                                    </div>
                                )}

                                {/* Content */}
                                <div className="mb-4">
                                    <h6>Content</h6>

                                    <div className="border rounded p-3 bg-light">
                                        {post.content}
                                    </div>
                                </div>

                                {/* Images */}
                                {post.images?.length > 0 && (
                                    <div className="mb-4">
                                        <h6>Images</h6>

                                        <div className="row">
                                            {post.images.map(
                                                (
                                                    image: string,
                                                    index: number
                                                ) => (
                                                    <div
                                                        className="col-md-3 mb-3"
                                                        key={index}
                                                    >
                                                        <PostThumbnail
                                                            src={image}
                                                            alt={`Post Image ${index + 1}`}
                                                            width="100%"
                                                            height={200}
                                                            rounded="8px"
                                                            className="w-100"
                                                        />
                                                    </div>
                                                )
                                            )}
                                        </div>
                                    </div>
                                )}

                                {/* Videos */}
                                {post.videos?.length > 0 && (
                                    <div className="mb-4">
                                        <h6>Videos</h6>

                                        {post.videos.map(
                                            (
                                                video: string,
                                                index: number
                                            ) => (
                                                <video
                                                    key={index}
                                                    controls
                                                    className="w-100 mb-3 rounded"
                                                >
                                                    <source
                                                        src={video}
                                                    />
                                                </video>
                                            )
                                        )}
                                    </div>
                                )}

                                {/* Audios */}
                                {post.audios?.length > 0 && (
                                    <div className="mb-4">
                                        <h6>Audios</h6>

                                        {post.audios.map(
                                            (
                                                audio: string,
                                                index: number
                                            ) => (
                                                <audio
                                                    key={index}
                                                    controls
                                                    className="w-100 mb-2"
                                                >
                                                    <source
                                                        src={audio}
                                                    />
                                                </audio>
                                            )
                                        )}
                                    </div>
                                )}

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </>
    );
}