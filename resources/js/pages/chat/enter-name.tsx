import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { MessageCircle } from 'lucide-react';

export default function EnterName() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/chat/room');
    };

    return (
        <>
            <Head title="Join Chat" />
            
            <div className="min-h-screen bg-[#0a1628] flex items-center justify-center p-4">
                <div className="w-full max-w-md bg-[#112240] rounded-2xl shadow-2xl p-8 border border-[#233554]">
                    <div className="flex flex-col items-center mb-8">
                        <div className="w-16 h-16 bg-[#00a884] rounded-full flex items-center justify-center mb-4 shadow-lg shadow-[#00a884]/20">
                            <MessageCircle className="w-8 h-8 text-white" />
                        </div>
                        <h1 className="text-2xl font-bold text-white mb-2 tracking-tight">Welcome to Chat</h1>
                        <p className="text-[#8892b0] text-center text-sm">
                            Enter your display name to join the global chat room
                        </p>
                    </div>

                    <form onSubmit={submit} className="space-y-6">
                        <div>
                            <input
                                type="text"
                                name="name"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="w-full bg-[#0a1628] border border-[#233554] rounded-xl px-4 py-3 text-white placeholder-[#8892b0] focus:outline-none focus:border-[#00a884] focus:ring-1 focus:ring-[#00a884] transition-all"
                                placeholder="e.g. Your name"
                                required
                                minLength={2}
                                maxLength={50}
                                autoComplete="off"
                            />
                            {errors.name && (
                                <p className="mt-2 text-sm text-red-400">{errors.name}</p>
                            )}
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full bg-[#00a884] hover:bg-[#008f6f] text-white font-semibold py-3 px-4 rounded-xl transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-[#00a884]/20"
                        >
                            {processing ? 'Joining...' : 'Start Chatting'}
                        </button>
                    </form>
                </div>
                
                {/* Background decorative elements */}
                <div className="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
                    <div className="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-[#00a884] opacity-5 blur-[120px]"></div>
                    <div className="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-[#00a884] opacity-5 blur-[120px]"></div>
                </div>
            </div>
        </>
    );
}
