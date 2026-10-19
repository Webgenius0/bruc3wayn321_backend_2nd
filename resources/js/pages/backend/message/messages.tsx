import { router, usePage } from "@inertiajs/react";
import './messages.css';
import type { Auth } from '@/types';
import { UserAvatar } from '@/components/ui/user-avatar';

interface User {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    avatar: string | null;
}

interface Chat {
    id: number;
    is_group: boolean;
    users: User[];
    last_message: string;
    last_message_time: string;
    unread_count: number;
}

interface Props {
    chats: Chat[];
    selectedChat?: Chat;
    messages: Message[];

}

interface Message {
    id: number;
    message: string;
    user_id: number;
    created_at: string;
    sender: User;
}

const limitWords = (text: string | null | undefined, limit: number) => {
    if (!text) return "";
    const words = text.split(" ");

    if (words.length <= limit) return text;

    return words.slice(0, limit).join(" ") + "...";
};
const timeFormat = (time: string) => {
    return new Date(time).toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
    });
};


const MessageFormatter = ({ text }: { text?: string }) => {
    if (!text) return null;
    const urlRegex = /(https?:\/\/[^\s]+)/g;
    const parts = text.split(urlRegex);

    return (
        <span style={{ whiteSpace: 'pre-wrap', wordBreak: 'break-word' }}>
            {parts.map((part, index) => {
                if (part.match(urlRegex)) {
                    return (
                        <a
                            key={index}
                            href={part}
                            target="_blank"
                            rel="noopener noreferrer"
                            style={{ color: 'inherit', textDecoration: 'underline', fontWeight: 500 }}
                        >
                            {part}
                        </a>
                    );
                }
                return <span key={index}>{part}</span>;
            })}
        </span>
    );
};

const handleChatClick = (chat: Chat) => {
    router.get('/admin/messages', { chat_id: chat.id }, { preserveState: true, preserveScroll: true, only: ['selectedChat', 'messages'] });
};


export default function Messages({ chats, selectedChat, messages }: Props) {
    const { auth } = usePage<{ auth: Auth }>().props;
    const authUser = auth.user;

    const exportConversation = () => {
        if (!selectedChat || !messages || messages.length === 0) return;

        let content = `Chat Export\n`;
        content += `Exported on: ${new Date().toLocaleString()}\n\n`;

        messages.forEach((msg) => {
            const msgUser = selectedChat.users.find(u => u.id === msg.user_id) || authUser;
            const senderName = msg.user_id === authUser.id ? 'Me' : (msgUser.first_name ? `${msgUser.first_name} ${msgUser.last_name}` : 'Unknown User');
            const time = timeFormat(msg.created_at);
            content += `[${time}] ${senderName}: ${msg.message}\n`;
        });

        const blob = new Blob([content], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `chat_export_${selectedChat.id}_${new Date().getTime()}.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    };

    console.log(messages);

    return <>

        <div className="row">
            <div className="col-lg-3 col-xxl-3">
                <div className="card bg-white rounded-10 border border-white mb-4 pb-20">
                    <div className="p-20 pb-10">
                        <h3 className="mb-20">
                            Messages
                        </h3>
                        {/* <form className="table-src-form position-relative ms-0 mb-20">
                            <input className="form-control w-100" placeholder="Search..." style={{ height: "45px" }} type="text" />
                            <div className="src-btn position-absolute top-50 start-0 translate-middle-y bg-transparent p-0 border-0">
                                <span className="material-symbols-outlined">
                                    search
                                </span>
                            </div>
                        </form> */}
                    </div>
                    <div className="scroll-bar" style={{ height: "630px", margin: "0 -1px" }}>

                        <ul className="p-0 mb-0 list-unstyled chat-list last-child-only-border-none">
                            {chats.map(chat => (

                                <li
                                    key={chat.id}
                                    className="border-bottom border-light d-flex justify-content-between align-items-center"
                                    style={{ padding: "16px 20px", cursor: "pointer" }}
                                    onClick={() => handleChatClick(chat)}
                                >
                                    <div className="d-flex align-items-center">
                                        <div className="flex-shrink-0 position-relative">
                                            <div className="d-flex align-items-center">
                                                {chat.users.slice(0, 2).map((user) => (
                                                    <UserAvatar
                                                        key={user.id}
                                                        src={user.avatar}
                                                        name={`${user.first_name || ''} ${user.last_name || ''}`.trim() || 'User'}
                                                        size={45}
                                                        className="border border-white"
                                                        style={{ marginLeft: "-8px" }}
                                                    />
                                                ))}
                                            </div>
                                        </div>

                                        <div className="flex-grow-1 ms-3">
                                            <h4 className="fs-16 fw-medium mb-0">
                                                {chat.users
                                                    .slice(0, 2)
                                                    .map(user => `${user.first_name} ${user.last_name}`)
                                                    .join(" & ")}
                                            </h4>

                                            <span className="fs-14 text-body">
                                                {limitWords(chat.last_message, 3)}
                                            </span>
                                        </div>
                                    </div>

                                    <div className="text-end">
                                        <span className="fs-14 d-block">
                                            {timeFormat(chat.last_message_time)}
                                        </span>

                                        {chat.unread_count > 0 && (
                                            <span
                                                className="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger text-white fs-11 mt-1"
                                                style={{ width: 18, height: 18 }}
                                            >
                                                {chat.unread_count}
                                            </span>
                                        )}
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>
            </div>

            <div className={selectedChat ? "col-lg-6 col-xxl-6" : "col-lg-9 col-xxl-9"}>
                {selectedChat ? (
                    <div className="card bg-white rounded-10 border border-white mb-4">
                        <div className="p-20 d-flex justify-content-between align-items-center border-bottom border-border-color-60 chat-header">
                            {selectedChat.users.map((user) => (
                                <div key={user.id} className="d-flex align-items-center">
                                    <div className="flex-shrink-0 position-relative">
                                        <UserAvatar
                                            src={user.avatar}
                                            name={`${user.first_name || ''} ${user.last_name || ''}`.trim() || 'User'}
                                            size={60}
                                            showStatus
                                        />
                                    </div>
                                    <div className="flex-grow-1 ms-3 position-relative top-3">
                                        <h4 className="fs-18 fw-medium mb-0">
                                            {user.first_name} {user.last_name}
                                        </h4>
                                        <span className="fs-15 text-success d-block" style={{ marginTop: "5px" }}>
                                            {user.email}
                                        </span>
                                    </div>
                                </div>
                            ))}
                        </div>
                        <div className="chat-body scroll-bar" style={{ height: "585px", overflowY: "auto" }}>
                            <ul className="p-20 mb-0 list-unstyled chat-details chat-reverse">
                                {messages?.map((msg) => {
                                    const isOwnMessage = msg.user_id === authUser.id;
                                    const msgUser =
                                        selectedChat?.users.find((u) => u.id === msg.user_id) || authUser;

                                    if (isOwnMessage) {
                                        return (
                                            <li key={msg.id} className="mb-20">
                                                <div className="d-flex text-end">
                                                    <div className="flex-grow-1">
                                                        <div>
                                                            <p
                                                                className="lh-1-8 bg-primary text-white fs-16 d-inline-block"
                                                                style={{
                                                                    padding: "10px 15px",
                                                                    borderRadius: "10px 0 10px 10px",
                                                                    marginBottom: "6px",
                                                                }}
                                                            >
                                                                <MessageFormatter text={msg.message} />
                                                            </p>
                                                        </div>

                                                        <span className="fs-14 d-block">
                                                            {timeFormat(msg.created_at)}
                                                        </span>
                                                    </div>

                                                    <div className="flex-shrink-0 ms-10">
                                                        <UserAvatar
                                                            src={msgUser.avatar}
                                                            name={`${msgUser.first_name || ''} ${msgUser.last_name || ''}`.trim() || 'User'}
                                                            size={35}
                                                        />
                                                    </div>
                                                </div>
                                            </li>
                                        );
                                    }

                                    return (
                                        <li key={msg.id} className="mb-20">
                                            <div className="d-flex">
                                                <div className="flex-shrink-0 me-10">
                                                    <UserAvatar
                                                        src={msg.sender?.avatar}
                                                        name={`${msg.sender?.first_name || ''} ${msg.sender?.last_name || ''}`.trim() || 'User'}
                                                        size={35}
                                                    />
                                                </div>

                                                <div className="flex-grow-1 ms-10">
                                                    <div>
                                                        <p className="mb-0" style={{ fontSize: "14px" }}>{msg.sender.first_name} {msg.sender.last_name}</p>
                                                        <p
                                                            className="lh-1-8 bg-light fs-16 d-inline-block"
                                                            style={{
                                                                padding: "10px 15px",
                                                                borderRadius: "0 10px 10px 10px",
                                                                marginBottom: "6px",
                                                            }}
                                                        >
                                                            <MessageFormatter text={msg.message} />
                                                        </p>
                                                    </div>

                                                    <span className="fs-14 d-block">
                                                        {timeFormat(msg.created_at)}
                                                    </span>
                                                </div>
                                            </div>
                                        </li>
                                    );
                                })}
                            </ul>
                        </div>

                    </div>
                ) : (
                    <div className="card bg-white rounded-10 border border-white mb-4 d-flex align-items-center justify-content-center" style={{ height: "100%" }}>
                        <p className="fs-18 text-muted">Select a chat to view messages</p>
                    </div>
                )}
            </div>


            <div className="col-lg-3 col-xxl-3">
                {selectedChat && (
                    <div className="card bg-white rounded-10 border border-white mb-4 pb-20">
                        <div className="p-20">
                            <div className="p-3">
                                <h5>Conversation Info</h5>
                                <table className="table table-sm">
                                    <tbody >
                                        <tr><td className="bg-white">Participants</td><td className="text-end bg-white">{selectedChat.users.length + 1}</td></tr>
                                        <tr><td className="bg-white">Status</td><td className="text-end bg-white"><span className="badge bg-success">Active</span></td></tr>
                                        <tr><td className="bg-white">Total Messages</td><td className="text-end bg-white">{messages.length}</td></tr>
                                    </tbody>
                                </table>

                                <h6 className="mt-4">Quick Actions</h6>
                                <div className="d-grid gap-2">
                                    <button onClick={exportConversation} className="btn btn-outline-secondary">Export Conversation</button>
                                    {/* <button className="btn btn-outline-warning">Mark as Important</button>
                                    <button className="btn btn-outline-danger">Block User</button> */}
                                </div>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </div>
    </>

}