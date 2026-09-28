import React, { useState } from 'react';

import { Head, Link, router, usePage, } from '@inertiajs/react';
import { toast } from 'sonner';
import Swal from "sweetalert2";


interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedPosts {
    data: Post[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: PaginationLink[];
}

interface Props {
    posts: PaginatedPosts;
    filters: {
        publisher?: string;
        date_from?: string;
        date_to?: string;
    };
}

interface Post {
    id: number;
    content?: any;
    user?: any;
    total_likes?: number;
    total_comments?: number;
    total_views?: number;
    image?: string;
    created_at_formatted?: string;
    slug?: string;
}


export default function PostIndex({ posts, filters }: Props) {
    //   const { posts, filters } = usePage().props as any;
    const [publisher, setPublisher] = useState(filters?.publisher || '');
    const [dateFrom, setDateFrom] = useState(filters?.date_from || '');
    const [dateTo, setDateTo] = useState(filters?.date_to || '');

    const handleSearch = () => {
        router.get(
            '/admin/posts',
            {
                publisher,
                date_from: dateFrom,
                date_to: dateTo,
            },
            {
                preserveState: true,
                preserveScroll: true,
            }
        );
    };

    const deletePost = async (slug = '') => {
        if (!confirm("Are you sure you want to delete this post?")) {
            return;
        }

        try {
            await router.delete(`/posts/${slug}`);
            toast.success("Post deleted successfully");
        } catch (error) {
            toast.error("Failed to delete post");
        }
    };


    console.log(posts);


    return (

        <>
            <Head title="Post List" />

            <div className="main-content-container overflow-hidden" style={{ minHeight: "75vh" }}>
                <div className="row">
                    <div className="col-md-12">
                        <div className="card bg-white rounded-10 border border-white mb-4">
                            <div className="d-flex justify-content-between align-items-center flex-wrap gap-3 p-20">
                                {/* Filters */}
                                <div className="d-flex align-items-center gap-2 flex-wrap">
                                    <div className="d-flex gap-2 ">
                                        <input
                                            className="form-control"
                                            placeholder="Search publisher"
                                            type="text"
                                            value={publisher}
                                            onChange={(e) => setPublisher(e.target.value)}
                                        // style={{ width: '250px' }}
                                        />
                                        <input
                                            className="form-control"
                                            type="date"
                                            value={dateFrom}
                                            onChange={(e) => setDateFrom(e.target.value)}
                                        />
                                        <input
                                            className="form-control"
                                            type="date"
                                            value={dateTo}
                                            onChange={(e) => setDateTo(e.target.value)}
                                        />
                                    </div>
                                    <button
                                        className="btn btn-primary text-white"
                                        onClick={handleSearch}
                                    >
                                        Filter
                                    </button>
                                </div>

                            </div>

                            <div className="default-table-area mx-minus-1 table-to-do-list">

                                <div className="table-responsive">

                                    <table className="table align-middle w-100">

                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Thumbnail</th>
                                                <th>Content</th>
                                                <th>Publisher</th>
                                                <th>Activity</th>
                                                <th>Created At</th>
                                                <th className="text-end">Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            {posts?.data?.length > 0 ? (
                                                posts.data.map((post: Post) => (
                                                    // console.log(post.images),
                                                    <tr key={post.id}>
                                                        <td>{post.id}</td>
                                                        <td>
                                                            {post.image ? (
                                                                <img
                                                                    src={`/${post.image}`}
                                                                    alt="Post"
                                                                    width={60}
                                                                    height={60}
                                                                    style={{ objectFit: "cover", borderRadius: "6px" }}
                                                                />
                                                            ) : (
                                                                <div
                                                                    style={{ width: 60, height: 60, backgroundColor: "#eee", borderRadius: "6px" }}
                                                                    className="d-flex align-items-center justify-content-center text-muted fs-12"
                                                                >
                                                                    No Image
                                                                </div>
                                                            )}
                                                        </td>
                                                        <td>
                                                            {post.content?.length > 80
                                                                ? `${post.content.substring(0, 80)}...`
                                                                : post.content}
                                                        </td>
                                                        <td>
                                                            {post?.user?.first_name} {post?.user?.last_name}
                                                        </td>
                                                        <td>
                                                            <table>
                                                                <tbody>
                                                                    <tr>
                                                                        <td style={{ fontSize: "14px" }} className='p-0 '>Likes:</td>
                                                                        <td style={{ fontSize: "14px" }} className='p-0  ps-2'><span className="badge bg-primary">{post?.total_likes ?? 0}</span></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style={{ fontSize: "14px" }} className='p-0 '>Comments:</td>
                                                                        <td style={{ fontSize: "14px" }} className='p-0  ps-2'><span className="badge bg-primary">{post?.total_comments ?? 0}</span></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style={{ fontSize: "14px" }} className='p-0 '>Views:</td>
                                                                        <td style={{ fontSize: "14px" }} className='p-0  ps-2'><span className="badge bg-primary">{post?.total_views ?? 0}</span></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                            {/* Likes: <span className="badge bg-primary">{post?.total_likes ?? 0}</span> <br />
                                                            Comments: <span className="badge bg-primary">{post?.total_comments ?? 0}</span> <br />
                                                            Views: <span className="badge bg-primary">{post?.total_views ?? 0}</span> */}
                                                        </td>

                                                        <td>{post.created_at_formatted}</td>
                                                        <td>
                                                            <div
                                                                className="d-flex justify-content-end"
                                                                style={{ gap: "12px" }}
                                                            >
                                                                <Link
                                                                    className="bg-transparent p-0 border-0 hover-text-success"
                                                                    href={`/admin/post/show/${post.slug}`}
                                                                >
                                                                    <i className="material-symbols-outlined fs-16 fw-normal text-body">
                                                                        Visibility
                                                                    </i>
                                                                </Link>

                                                                <Link
                                                                    className="bg-transparent p-0 border-0 hover-text-success"
                                                                    href={`/admin/post/edit/${post.slug}`}
                                                                >
                                                                    <i className="material-symbols-outlined fs-16 fw-normal text-body">
                                                                        edit
                                                                    </i>
                                                                </Link>

                                                                <button
                                                                    onClick={() => deletePost(post.slug)}
                                                                    className="bg-transparent p-0 border-0 hover-text-danger"
                                                                >
                                                                    <i className="material-symbols-outlined fs-16 fw-normal text-body">
                                                                        delete
                                                                    </i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                ))
                                            ) : (
                                                <tr>
                                                    <td className="text-center py-4">
                                                        No record available yet.
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>

                                    </table>



                                    <div className="d-flex justify-content-between align-items-center p-3">

                                        <div>
                                            Showing {posts?.from ?? 0} - {posts?.to ?? 0} of {posts?.total ?? 0} records
                                        </div>

                                        <div className="d-flex justify-content-end mt-3">
                                            <ul className="pagination mb-0">

                                                {posts?.links?.map((link, index) => (
                                                    <li
                                                        key={index}
                                                        className={`page-item ${link.active ? "active" : ""
                                                            } ${!link.url ? "disabled" : ""}`}
                                                    >
                                                        <button
                                                            className="page-link"
                                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                                            disabled={!link.url}
                                                            onClick={() => {
                                                                if (link.url) {
                                                                    router.visit(link.url, {
                                                                        preserveState: true,
                                                                        preserveScroll: true,
                                                                    });
                                                                }
                                                            }}
                                                        />
                                                    </li>
                                                ))}

                                            </ul>
                                        </div>
                                    </div>

                                </div>



                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </>
    );
}
