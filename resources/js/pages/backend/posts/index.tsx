import React, { useState } from 'react';

import { Head, Link, router, usePage, } from '@inertiajs/react';
import { toast } from 'sonner';
import Swal from "sweetalert2";

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
    content?: string;
    publisher?: {
        name?: string;
    } | null;
    created_at?: string;
    slug?: string;
}


export default function PostIndex({ posts, filters }: Props) {
    //   const { posts, filters } = usePage().props as any;
    const [publisher, setPublisher] = useState(filters?.publisher || '');
    const [dateFrom, setDateFrom] = useState(filters?.date_from || '');
    const [dateTo, setDateTo] = useState(filters?.date_to || '');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        // Inertia.get(route('admin.posts.index'), {
        //   publisher,
        //   date_from: dateFrom,
        //   date_to: dateTo,
        // }, { preserveState: true, preserveScroll: true });
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
            <Head title="User List" />

            <div
                className="main-content-container overflow-hidden"
                style={{ minHeight: "75vh" }}
            >

                <div className="row">

                    <div className="col-md-12">
                        <div className="card bg-white rounded-10 border border-white mb-4">

                            <div className="d-flex justify-content-between align-items-center flex-wrap gap-3 p-20">

                                <Link className="bg-transparent text-primary fs-16 border-0 p-0" href={'/admin/users/create'}>
                                    + Add New User
                                </Link>
                                {/* <button
                                    className="bg-transparent text-primary fs-16 border-0 p-0"
                                    data-bs-target="#exampleModal"
                                    data-bs-toggle="modal"
                                    onClick={() => {

                                        setEditId(null);

                                        reset();
                                    }}
                                >

                                    + Add New User

                                </button> */}

                                {/* Filters */}

                                <div className="d-flex align-items-center gap-2 flex-wrap">

                                    <input
                                        className="form-control"
                                        placeholder="Search by name or email"
                                        type="text"
                                        // value={search}
                                        // onChange={(e) =>
                                        //     setSearch(e.target.value)
                                        // }
                                        style={{ width: '250px' }}
                                    />

                                    <input
                                        className="form-control"
                                        type="date"
                                        // value={joinDate}
                                        // onChange={(e) =>
                                        //     setJoinDate(e.target.value)
                                        // }
                                        style={{ width: '250px' }}
                                    />

                                    <button
                                        className="btn btn-primary text-white"
                                    // onClick={}
                                    >

                                        Filter

                                    </button>

                                    <button
                                        className="btn btn-secondary"
                                    // onClick={}
                                    >

                                        Reset

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
                                                            asdf
                                                        </td>

                                                        <td>
                                                            {post.publisher?.name || "N/A"}
                                                        </td>

                                                        <td>{post.created_at}</td>

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
                                                    <td  className="text-center py-4">
                                                        No record available yet.
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>

                                    </table>

                                </div>



                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </>
    );
}
