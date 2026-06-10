import React, { useState, useEffect } from 'react';
// import { Inertia } from '@inertiajs/react';
import { Head, Link, usePage } from '@inertiajs/react';

// import route from '@/routes';

/**
 * Admin Post Edit Page
 * Provides a comprehensive form for every attribute of the Post model.
 */
export default function PostEdit() {
  const { post } = usePage().props as any;
  const { users } = usePage().props as any;

  // State for each column in the Post model
  const [userId, setUserId] = useState(post?.user_id ?? '');
  const [slug, setSlug] = useState(post?.slug ?? '');
  const [content, setContent] = useState(post?.content ?? '');
  const [location, setLocation] = useState(post?.location ?? '');
  const [latitude, setLatitude] = useState(post?.latitude ?? '');
  const [longitude, setLongitude] = useState(post?.longitude ?? '');
  const [images, setImages] = useState(JSON.stringify(post?.images ?? []));
  const [audios, setAudios] = useState(JSON.stringify(post?.audios ?? []));
  const [videos, setVideos] = useState(JSON.stringify(post?.videos ?? []));
  const [isDraft, setIsDraft] = useState(!!post?.is_draft);
  const [isPublished, setIsPublished] = useState(!!post?.is_published);

  // Keep state in sync if the prop changes (unlikely after navigation)
  useEffect(() => {
    if (post) {
      setUserId(post.user_id ?? '');
      setSlug(post.slug ?? '');
      setContent(post.content ?? '');
      setLocation(post.location ?? '');
      setLatitude(post.latitude ?? '');
      setLongitude(post.longitude ?? '');
      setImages(JSON.stringify(post.images ?? []));
      setAudios(JSON.stringify(post.audios ?? []));
      setVideos(JSON.stringify(post.videos ?? []));
      setIsDraft(!!post.is_draft);
      setIsPublished(!!post.is_published);
    }
  }, [post]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

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
                    <label className="block">
                      <span className="text-gray-700">
                        Publisher (User)
                      </span>

                      <select
                        name="user_id"
                        className="form-control"
                        value={userId}
                        onChange={(e) => setUserId(e.target.value)}
                        required
                      >
                        <option value="">
                          Select User
                        </option>

                        {users.map((user) => (
                          <option
                            key={user.id}
                            value={user.id}
                          >
                            {user.name} ({user.email})
                          </option>
                        ))}
                      </select>
                    </label>
                    <label className="block">
                      <span className="text-gray-700">Slug</span>
                      <input
                        type="text"
                        value={slug}
                        onChange={e => setSlug(e.target.value)}
                        className="mt-1 block w-full border rounded px-3 py-2"
                        required
                      />
                    </label>
                    <label className="block">
                      <span className="text-gray-700">Content</span>
                      <textarea
                        rows={6}
                        value={content}
                        onChange={e => setContent(e.target.value)}
                        className="mt-1 block w-full border rounded px-3 py-2"
                        required
                      />
                    </label>
                    <label className="block">
                      <span className="text-gray-700">Location</span>
                      <input
                        type="text"
                        value={location}
                        onChange={e => setLocation(e.target.value)}
                        className="mt-1 block w-full border rounded px-3 py-2"
                      />
                    </label>
                    <div className="grid grid-cols-2 gap-4">
                      <label className="block">
                        <span className="text-gray-700">Latitude</span>
                        <input
                          type="number"
                          step="any"
                          value={latitude}
                          onChange={e => setLatitude(e.target.value)}
                          className="mt-1 block w-full border rounded px-3 py-2"
                        />
                      </label>
                      <label className="block">
                        <span className="text-gray-700">Longitude</span>
                        <input
                          type="number"
                          step="any"
                          value={longitude}
                          onChange={e => setLongitude(e.target.value)}
                          className="mt-1 block w-full border rounded px-3 py-2"
                        />
                      </label>
                    </div>
                    <label className="block">
                      <span className="text-gray-700">Images (JSON array)</span>
                      <textarea
                        rows={3}
                        value={images}
                        onChange={e => setImages(e.target.value)}
                        className="mt-1 block w-full border rounded px-3 py-2"
                      />
                    </label>
                    <label className="block">
                      <span className="text-gray-700">Audios (JSON array)</span>
                      <textarea
                        rows={3}
                        value={audios}
                        onChange={e => setAudios(e.target.value)}
                        className="mt-1 block w-full border rounded px-3 py-2"
                      />
                    </label>
                    <label className="block">
                      <span className="text-gray-700">Videos (JSON array)</span>
                      <textarea
                        rows={3}
                        value={videos}
                        onChange={e => setVideos(e.target.value)}
                        className="mt-1 block w-full border rounded px-3 py-2"
                      />
                    </label>
                    <div className="flex items-center space-x-4">
                      <label className="inline-flex items-center">
                        <input
                          type="checkbox"
                          checked={isDraft}
                          onChange={e => setIsDraft(e.target.checked)}
                          className="form-checkbox"
                        />
                        <span className="ml-2">Draft</span>
                      </label>
                      <label className="inline-flex items-center">
                        <input
                          type="checkbox"
                          checked={isPublished}
                          onChange={e => setIsPublished(e.target.checked)}
                          className="form-checkbox"
                        />
                        <span className="ml-2">Published</span>
                      </label>
                    </div>
                    <div className="flex space-x-4">
                      <button
                        type="submit"
                        className="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
                      >
                        Save Changes
                      </button>
                      <Link onClick={() => window.history.back()} className="text-gray-600 hover:underline">
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
