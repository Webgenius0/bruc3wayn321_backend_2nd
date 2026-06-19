import React, { useState } from 'react';

import { Head, Link, router, usePage, } from '@inertiajs/react';
import { toast } from 'sonner';
import Swal from "sweetalert2";
interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PostsPagination {
    data: Post[];
    total: any,
    current_page: any,
    last_page: any,
    // total: number;
    links: PaginationLink[];
}

interface Props {
    posts: {
        data: Post[];
    };

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



    return (
        <>
            <Head title="Post List" />

            <div
                className="main-content-container overflow-hidden"
                style={{ minHeight: "75vh" }}
            >

                <div className="row">

                    <div className="col-md-12">
                        <div className="card bg-white rounded-10 border border-white mb-4">

                            <div className="d-flex justify-content-between align-items-center flex-wrap gap-3 p-20">
                                <div></div>
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
                                                <th>Content</th>
                                                <th>Publisher</th>
                                                <th>Created At</th>
                                                <th className="text-end">Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            {posts?.data?.length > 0 ? (
                                                posts.data.map((post: Post) => (

                                                    <tr key={post.id}>
                                                        <td>{post.id}</td>
                                                        <td>
                                                            {post.content?.length > 80
                                                                ? `${post.content.substring(0, 80)}...`
                                                                : post.content}
                                                        </td>

                                                        <td>
                                                            {post.user.first_name} {post.user.last_name}
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
                                            Showing {posts.data.length} of {posts?.total} records
                                        </div>

                                        <div className="d-flex justify-content-center mt-4">
                                            <ul className="pagination">

                                                {/* Previous */}
                                                <li className={`page-item ${posts.current_page === 1 ? 'disabled' : ''}`}>
                                                    <button
                                                        className="page-link"
                                                        onClick={() =>
                                                            router.get(
                                                                window.location.pathname,
                                                                {
                                                                    ...filters,
                                                                    page: posts.current_page - 1,
                                                                },
                                                                {
                                                                    preserveState: true,
                                                                    preserveScroll: true,
                                                                }
                                                            )
                                                        }
                                                    >
                                                        Previous
                                                    </button>
                                                </li>

                                                {Array.from({ length: posts.last_page }, (_, i) => i + 1)
                                                    .filter(
                                                        page =>
                                                            page === 1 ||
                                                            page === posts.last_page ||
                                                            Math.abs(page - posts.current_page) <= 2
                                                    )
                                                    .map((page, index, pages) => {
                                                        const prevPage = pages[index - 1];

                                                        return (
                                                            <React.Fragment key={page}>
                                                                {prevPage && page - prevPage > 1 && (
                                                                    <li className="page-item disabled">
                                                                        <span className="page-link">...</span>
                                                                    </li>
                                                                )}

                                                                <li
                                                                    className={`page-item ${posts.current_page === page ? 'active' : ''
                                                                        }`}
                                                                >
                                                                    <button
                                                                        className="page-link"
                                                                        onClick={() =>
                                                                            router.get(
                                                                                window.location.pathname,
                                                                                {
                                                                                    ...filters,
                                                                                    page,
                                                                                },
                                                                                {
                                                                                    preserveState: true,
                                                                                    preserveScroll: true,
                                                                                }
                                                                            )
                                                                        }
                                                                    >
                                                                        {page}
                                                                    </button>
                                                                </li>
                                                            </React.Fragment>
                                                        );
                                                    })}

                                                {/* Next */}
                                                <li
                                                    className={`page-item ${posts.current_page === posts.last_page ? 'disabled' : ''
                                                        }`}
                                                >
                                                    <button
                                                        className="page-link"
                                                        onClick={() =>
                                                            router.get(
                                                                window.location.pathname,
                                                                {
                                                                    ...filters,
                                                                    page: posts.current_page + 1,
                                                                },
                                                                {
                                                                    preserveState: true,
                                                                    preserveScroll: true,
                                                                }
                                                            )
                                                        }
                                                    >
                                                        Next
                                                    </button>
                                                </li>
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
