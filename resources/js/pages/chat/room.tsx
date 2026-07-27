import { Head, router } from '@inertiajs/react';
import { FormEventHandler, useEffect, useRef, useState } from 'react';
import { Send, Smile, Paperclip, MoreVertical, Search, LogOut } from 'lucide-react';
import axios from 'axios';

interface Message {
    id: string;
    body: string;
    mesage: string;
    sender: string;
    timestamp: string;
    isOwn: boolean;
}

export default function room({ username, chatId }: { username: string, chatId: number }) {
    const [messages, setMessages] = useState<Message[]>([]);
    const [newMessage, setNewMessage] = useState('');
    const [isTyping, setIsTyping] = useState(false);
    const messagesEndRef = useRef<HTMLDivElement>(null);
    const chatContainerRef = useRef<HTMLDivElement>(null);

    // Scroll to bottom when messages change
    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    useEffect(() => {
        scrollToBottom();
    }, [messages]);

    // Setup Echo listener
    useEffect(() => {
        if (!window.Echo) {
            console.error('Echo is not initialized');
            return;
        }

        const channel = window.Echo.channel(`chat.1`);

        channel.listen('SendMessage', (e: any) => {
            // Reverb wraps data based on broadcastAs
            const newMsg: Message = {
                id: Math.random().toString(36).substring(7),
                body: e.body,
                sender: e.sender,
                mesage: e.message.message,
                timestamp: e.timestamp,
                isOwn: e.sender === username
            };


            console.log('send message' + newMsg + "-"+ e)

            setMessages(prev => [...prev, newMsg]);
        });

        return () => {
            channel.stopListening('SendMessage');
            window.Echo.leave(`chat.${chatId}`);
        };
    }, [chatId, username]);

    const sendMessage: FormEventHandler = async (e) => {
        e.preventDefault();
        if (!newMessage.trim()) return;

        const messageText = newMessage;
        setNewMessage(messageText); // Optimistic clear

        try {
            await axios.post('/send-message', {
                sender: username,
                message: messageText,
                chat_id: chatId
            });

        } catch (error) {
            console.error('Failed to send message:', error);
            // Optionally restore message if failed
            // setNewMessage(messageText); 
        }
            const newMsg: Message = {
                id: Math.random().toString(36).substring(7),
                body: username ?? '',
                sender: username,
                mesage: messageText,
                timestamp: new Date().toISOString(),
                isOwn: true
            };

            console.log('send message' + newMsg)

            setMessages(prev => [...prev, newMsg]);
    };

    const handleLogout = () => {
        router.get('/chat'); // Goes back to name entry, destroying session naturally
    };

    const formatTime = (isoString: string) => {
        try {
            const date = new Date(isoString);
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        } catch (e) {
            return '';
        }
    };

    return (
        <>
            <Head title="Chat Room" />

            <div className="flex h-screen bg-[#0a1628] overflow-hidden font-sans">

                {/* Left Sidebar - Chat List */}
                <div className="hidden md:flex flex-col w-[350px] lg:w-[400px] border-r border-[#233554] bg-[#112240]">
                    {/* Sidebar Header */}
                    <div className="h-16 px-4 py-3 bg-[#0a1628] flex items-center justify-between border-b border-[#233554]">
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-full bg-[#00a884] flex items-center justify-center text-white font-bold text-lg">
                                {username.charAt(0).toUpperCase()}
                            </div>
                            <span className="text-white font-medium truncate max-w-[150px]">{username}</span>
                        </div>
                        <div className="flex items-center gap-4 text-[#8892b0]">
                            <button className="hover:text-white transition-colors"><MoreVertical size={20} /></button>
                        </div>
                    </div>

                    {/* Search */}
                    <div className="p-3 border-b border-[#233554]">
                        <div className="bg-[#0a1628] rounded-lg flex items-center px-3 py-1.5">
                            <Search size={18} className="text-[#8892b0] mr-3" />
                            <input
                                type="text"
                                placeholder="Search or start new chat"
                                className="bg-transparent border-none text-sm text-white focus:outline-none focus:ring-0 w-full placeholder-[#8892b0]"
                            />
                        </div>
                    </div>

                    {/* Chat List */}
                    <div className="flex-1 overflow-y-auto">
                        <div className="flex items-center p-3 cursor-pointer bg-[#233554] border-l-4 border-[#00a884]">
                            <div className="w-12 h-12 rounded-full bg-gradient-to-tr from-[#00a884] to-[#005c4b] flex items-center justify-center text-white font-bold text-xl mr-4 flex-shrink-0">
                                G
                            </div>
                            <div className="flex-1 min-w-0 border-b border-[#233554]/50 pb-3 mt-3">
                                <div className="flex justify-between items-baseline mb-1">
                                    <h2 className="text-white font-medium text-base truncate">Global Chat</h2>
                                    <span className="text-xs text-[#00a884]">Active</span>
                                </div>
                                <p className="text-sm text-[#8892b0] truncate">
                                    {messages.length > 0 ? messages[messages.length - 1].body : 'Say hello!'}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Right Area - Chat Area */}
                <div className="flex flex-col flex-1 bg-[#0a1628] relative">

                    {/* Chat Header */}
                    <div className="h-16 px-4 py-3 bg-[#112240] flex items-center justify-between border-b border-[#233554] z-10">
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-full bg-gradient-to-tr from-[#00a884] to-[#005c4b] flex items-center justify-center text-white font-bold text-lg">
                                G
                            </div>
                            <div>
                                <h2 className="text-white font-medium">Global Chat</h2>
                                <p className="text-xs text-[#8892b0]">Online</p>
                            </div>
                        </div>
                        <div className="flex items-center gap-4 text-[#8892b0]">
                            <Search size={20} className="hover:text-white cursor-pointer transition-colors" />
                            <button
                                onClick={handleLogout}
                                className="hover:text-red-400 cursor-pointer transition-colors flex items-center gap-2"
                                title="Leave Chat"
                            >
                                <LogOut size={20} />
                            </button>
                        </div>
                    </div>

                    {/* Chat Background Pattern */}
                    <div className="absolute inset-0 z-0 opacity-[0.03] pointer-events-none" style={{ backgroundImage: 'url("https://www.transparenttextures.com/patterns/cubes.png")' }}></div>

                    {/* Messages Area */}
                    <div
                        ref={chatContainerRef}
                        className="flex-1 overflow-y-auto p-4 sm:p-6 z-10 flex flex-col space-y-4"
                        style={{ scrollBehavior: 'smooth' }}
                    >
                        <div className="flex justify-center mb-6">
                            <span className="bg-[#112240] text-[#8892b0] text-xs px-3 py-1 rounded-lg border border-[#233554]">
                                Welcome to Global Chat
                            </span>
                        </div>

                        {messages.map((msg, index) => {
                            console.log(JSON.stringify(msg, null, 2));
                            const isOwn = msg.isOwn;
                            // Check if previous message was from same sender to group them
                            const prevMsg = index > 0 ? messages[index - 1] : null;
                            const showSender = !isOwn && (!prevMsg || prevMsg.sender !== msg.sender);
                            // console.log(showSender)

                            return (
                                <div key={msg.id} className={`flex ${isOwn ? 'justify-end' : 'justify-start'}`}>
                                    <div
                                        className={`max-w-[85%] md:max-w-[65%] lg:max-w-[50%] rounded-2xl px-4 py-2 relative shadow-sm ${isOwn
                                            ? 'bg-[#00a884] text-white rounded-tr-none'
                                            : 'bg-[#112240] text-white rounded-tl-none border border-[#233554]'
                                            }`}
                                    >
                                        {/* Sender Name for others */}
                                        {showSender && (
                                            <div className="text-xs font-semibold text-[#00a884] mb-1">
                                                {msg.sender}
                                            </div>
                                        )}

                                        {/* Message Body */}
                                        <div className="text-[15px] leading-relaxed break-words">
                                            {msg.mesage}
                                        </div>

                                        {/* Timestamp */}
                                        <div className={`text-[10px] mt-1 text-right flex items-center justify-end gap-1 ${isOwn ? 'text-white/80' : 'text-[#8892b0]'}`}>
                                            {formatTime(msg.timestamp)}
                                            {isOwn && (
                                                <svg viewBox="0 0 16 15" width="16" height="15" className="text-white/80"><path fill="currentColor" d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.879a.32.32 0 0 1-.484.033l-.358-.325a.319.319 0 0 0-.484.032l-.378.483a.418.418 0 0 0 .036.541l1.32 1.266c.143.14.361.125.484-.033l6.272-8.048a.366.366 0 0 0-.064-.512zm-4.1 0l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.879a.32.32 0 0 1-.484.033L1.891 7.769a.366.366 0 0 0-.515.006l-.423.433a.364.364 0 0 0 .006.514l3.258 3.185c.143.14.361.125.484-.033l6.272-8.048a.365.365 0 0 0-.063-.51z"></path></svg>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                        <div ref={messagesEndRef} />
                    </div>

                    {/* Input Area */}
                    <div className="h-[70px] px-4 py-3 bg-[#112240] flex items-center gap-3 border-t border-[#233554] z-10">
                        <button className="text-[#8892b0] hover:text-white transition-colors">
                            <Smile size={24} />
                        </button>
                        <button className="text-[#8892b0] hover:text-white transition-colors">
                            <Paperclip size={22} />
                        </button>

                        <form onSubmit={sendMessage} className="flex-1 flex items-center gap-3">
                            <input
                                type="text"
                                value={newMessage}
                                onChange={(e) => setNewMessage(e.target.value)}
                                placeholder="Type a message"
                                className="flex-1 bg-[#233554] border-none rounded-xl px-4 py-2.5 text-white placeholder-[#8892b0] focus:outline-none focus:ring-1 focus:ring-[#00a884] text-[15px]"
                                autoComplete="off"
                            />

                            <button
                                type="submit"
                                disabled={!newMessage.trim()}
                                className="w-10 h-10 rounded-full bg-[#00a884] flex items-center justify-center text-white disabled:opacity-50 disabled:cursor-not-allowed hover:bg-[#008f6f] transition-colors flex-shrink-0"
                            >
                                <Send size={18} className="ml-1" />
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </>
    );
}
