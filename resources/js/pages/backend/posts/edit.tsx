import React from 'react';
import { Head, Link, usePage, useForm } from '@inertiajs/react';

/**
 * Admin Post Edit Page
 * Provides a comprehensive form for every attribute of the Post model.
 */
export default function PostEdit() {
  const { post, users } = usePage().props as any;

  const { data, setData, post: submitPost, processing, errors } = useForm({
    _method: 'put',
    user_id: post?.user_id ?? '',
    slug: post?.slug ?? '',
    content: post?.content ?? '',
    location: post?.location ?? '',
    latitude: post?.latitude ?? '',
    longitude: post?.longitude ?? '',

    // Existing files (strings/paths)
    existing_images: post?.images ?? [],
    existing_audios: post?.audios ?? [],
    existing_videos: post?.videos ?? [],

    // New files to upload (File objects)
    images: [] as File[],
    audios: [] as File[],
    videos: [] as File[],

    is_draft: !!post?.is_draft,
    is_published: !!post?.is_published,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    submitPost(`/admin/posts/${post.slug}`, {
      forceFormData: true,
      preserveScroll: true,
    });
  };

  return (
    <>
      <Head title="Post Details" />

      <div
        className="main-content-container overflow-hidden"
        style={{ minHeight: "75vh" }}
      >
        <div className="row">
          <div className="col-lg-8">
            <div className="card bg-white p-4 border-0">
              <div className="card-body">
                <div className="container mx-auto p-4">
                  <h1 className="text-2xl font-bold mb-4">Edit Post</h1>
                  <form onSubmit={handleSubmit} className="grid gap-4 max-w-2xl">
                    <div className="block">
                      <span className="text-gray-700">
                        Publisher (User)
                      </span>
                      <select
                        name="user_id"
                        className="form-control"
                        value={data.user_id}
                        onChange={(e) => setData('user_id', e.target.value)}
                        required
                      >
                        <option value="">Select User</option>
                        {users.map((user: any) => (
                          <option key={user.id} value={user.id}>
                            {user.name || user.first_name} ({user.email})
                          </option>
                        ))}
                      </select>
                      {errors.user_id && <div className="text-danger mt-1 fs-14">{errors.user_id}</div>}
                    </div>

                    <div className="block mt-3">
                      <span className="text-gray-700">Slug</span>
                      <input
                        type="text"
                        value={data.slug}
                        onChange={e => setData('slug', e.target.value)}
                        className="mt-1 block w-full border rounded px-3 py-2"
                        required
                      />
                      {errors.slug && <div className="text-danger mt-1 fs-14">{errors.slug}</div>}
                    </div>

                    <div className="block mt-3">
                      <span className="text-gray-700">Content</span>
                      <textarea
                        rows={6}
                        value={data.content}
                        onChange={e => setData('content', e.target.value)}
                        className="mt-1 block w-full border rounded px-3 py-2"
                        required
                      />
                      {errors.content && <div className="text-danger mt-1 fs-14">{errors.content}</div>}
                    </div>

                    <div className="block mt-3">
                      <span className="text-gray-700">Location</span>
                      <input
                        type="text"
                        value={data.location}
                        onChange={e => setData('location', e.target.value)}
                        className="mt-1 block w-full border rounded px-3 py-2"
                      />
                    </div>

                    <div className="grid grid-cols-2 gap-4 mt-3 d-flex gap-3">
                      <div className="block flex-grow-1">
                        <span className="text-gray-700">Latitude</span>
                        <input
                          type="number"
                          step="any"
                          value={data.latitude}
                          onChange={e => setData('latitude', e.target.value)}
                          className="mt-1 block w-full border rounded px-3 py-2"
                        />
                      </div>
                      <div className="block flex-grow-1">
                        <span className="text-gray-700">Longitude</span>
                        <input
                          type="number"
                          step="any"
                          value={data.longitude}
                          onChange={e => setData('longitude', e.target.value)}
                          className="mt-1 block w-full border rounded px-3 py-2"
                        />
                      </div>
                    </div>

                    {/* Images Section */}
                    <div className="block mt-4 pt-3 border-top">
                      <div className="d-flex justify-content-between align-items-center mb-2">
                        <span className="text-gray-700 font-medium fs-18">Images</span>
                        <div>
                          <input
                            type="file"
                            multiple
                            accept="image/*"
                            id="upload-images"
                            className="d-none"
                            onChange={e => setData('images', Array.from(e.target.files || []))}
                          />
                          <label htmlFor="upload-images" className="btn btn-outline-primary btn-sm m-0">
                            + Add New Images
                          </label>
                        </div>
                      </div>

                      {data.images.length > 0 && (
                        <div className="mb-3 text-success fs-14">
                          {data.images.length} new image(s) selected for upload.
                        </div>
                      )}
                      {errors.images && <div className="text-danger mb-2 fs-14">{errors.images}</div>}

                      <div className="d-flex flex-wrap gap-3">
                        {data.existing_images.length === 0 ? (
                          <span className="text-muted fs-14">No existing images.</span>
                        ) : (
                          data.existing_images.map((item: string, i: number) => (
                            <div key={i} className="position-relative" style={{ width: '120px', height: '120px' }}>
                              <img src={`/${item}`} alt="preview" style={{ width: '100%', height: '100%', objectFit: 'cover', borderRadius: '8px', border: '1px solid #ddd' }} />
                              <button type="button" onClick={() => {
                                let newArr = [...data.existing_images];
                                newArr.splice(i, 1);
                                setData('existing_images', newArr);
                              }} className="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle d-flex align-items-center justify-content-center" style={{ width: '24px', height: '24px', padding: 0 }}>
                                <i className="material-symbols-outlined fs-16">close</i>
                              </button>
                            </div>
                          ))
                        )}
                      </div>
                    </div>

                    {/* Audios Section */}
                    <div className="block mt-4 pt-3 border-top">
                      <div className="d-flex justify-content-between align-items-center mb-2">
                        <span className="text-gray-700 font-medium fs-18">Audios</span>
                        <div>
                          <input
                            type="file"
                            multiple
                            accept="audio/*"
                            id="upload-audios"
                            className="d-none"
                            onChange={e => setData('audios', Array.from(e.target.files || []))}
                          />
                          <label htmlFor="upload-audios" className="btn btn-outline-primary btn-sm m-0">
                            + Add New Audios
                          </label>
                        </div>
                      </div>

                      {data.audios.length > 0 && (
                        <div className="mb-3 text-success fs-14">
                          {data.audios.length} new audio(s) selected for upload.
                        </div>
                      )}
                      {errors.audios && <div className="text-danger mb-2 fs-14">{errors.audios}</div>}

                      <div className="d-flex flex-column gap-3">
                        {data.existing_audios.length === 0 ? (
                          <span className="text-muted fs-14">No existing audios.</span>
                        ) : (
                          data.existing_audios.map((item: string, i: number) => (
                            <div key={i} className="d-flex align-items-center gap-3 bg-light p-2 rounded" style={{ maxWidth: '400px' }}>
                              <audio controls src={`/${item}`} style={{ height: '40px', flexGrow: 1 }} />
                              <button type="button" onClick={() => {
                                let newArr = [...data.existing_audios];
                                newArr.splice(i, 1);
                                setData('existing_audios', newArr);
                              }} className="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center px-2">
                                <i className="material-symbols-outlined fs-18">delete</i>
                              </button>
                            </div>
                          ))
                        )}
                      </div>
                    </div>

                    {/* Videos Section */}
                    <div className="block mt-4 pt-3 border-top mb-4">
                      <div className="d-flex justify-content-between align-items-center mb-2">
                        <span className="text-gray-700 font-medium fs-18">Videos</span>
                        <div>
                          <input
                            type="file"
                            multiple
                            accept="video/*"
                            id="upload-videos"
                            className="d-none"
                            onChange={e => setData('videos', Array.from(e.target.files || []))}
                          />
                          <label htmlFor="upload-videos" className="btn btn-outline-primary btn-sm m-0">
                            + Add New Videos
                          </label>
                        </div>
                      </div>

                      {data.videos.length > 0 && (
                        <div className="mb-3 text-success fs-14">
                          {data.videos.length} new video(s) selected for upload.
                        </div>
                      )}
                      {errors.videos && <div className="text-danger mb-2 fs-14">{errors.videos}</div>}

                      <div className="d-flex flex-wrap gap-3">
                        {data.existing_videos.length === 0 ? (
                          <span className="text-muted fs-14">No existing videos.</span>
                        ) : (
                          data.existing_videos.map((item: string, i: number) => (
                            <div key={i} className="position-relative bg-light p-2 rounded border" style={{ width: '240px' }}>
                              <video controls src={`/${item}`} style={{ width: '100%', borderRadius: '6px', backgroundColor: '#000' }} />
                              <button type="button" onClick={() => {
                                let newArr = [...data.existing_videos];
                                newArr.splice(i, 1);
                                setData('existing_videos', newArr);
                              }} className="btn btn-danger btn-sm mt-2 w-100 d-flex align-items-center justify-content-center gap-1">
                                <i className="material-symbols-outlined fs-16">delete</i> Remove
                              </button>
                            </div>
                          ))
                        )}
                      </div>
                    </div>

                    <div className="flex items-center space-x-4 mt-3">
                      <label className="inline-flex items-center me-4">
                        <input
                          type="checkbox"
                          checked={data.is_draft}
                          onChange={e => setData('is_draft', e.target.checked)}
                          className="form-check-input"
                          style={{ width: "20px", height: "20px", marginTop: "2px" }}
                        />
                        <span className="ml-2 ms-2">Draft</span>
                      </label>
                      <label className="inline-flex items-center">
                        <input
                          type="checkbox"
                          checked={data.is_published}
                          onChange={e => setData('is_published', e.target.checked)}
                          className="form-check-input"
                          style={{ width: "20px", height: "20px", marginTop: "2px" }}
                        />
                        <span className="ml-2 ms-2">Published</span>
                      </label>
                    </div>

                    <div className="flex space-x-4 mt-4 pt-3 border-top">
                      <button
                        type="submit"
                        disabled={processing}
                        className="btn btn-primary text-white px-4 py-2 rounded"
                      >
                        {processing ? 'Saving...' : 'Save Changes'}
                      </button>
                      <Link href="/admin/posts" className="btn btn-light ms-3">
                        Cancel
                      </Link>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
