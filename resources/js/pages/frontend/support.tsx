import { Head, usePage } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';

interface SupportPage {
    title: string;
    content: string;
}

export default function Support({ page }: { page?: SupportPage }) {
    const { setting } = usePage().props as any;
    const [form, setForm] = useState({ name: '', email: '', subject: '', message: '' });
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [notice, setNotice] = useState('');
    const [sending, setSending] = useState(false);

    const setField = (key: keyof typeof form, value: string) => setForm({ ...form, [key]: value });

    const submit = async (event: FormEvent) => {
        event.preventDefault();
        setSending(true);
        setNotice('');
        setErrors({});

        try {
            const response = await window.axios.post('/api/v1/support/issues', form);
            setNotice(response.data.message);
            setForm({ name: '', email: '', subject: '', message: '' });
        } catch (error: any) {
            setErrors(error.response?.data?.errors || {});
            setNotice(error.response?.data?.message || 'Unable to submit your request. Please try again.');
        } finally {
            setSending(false);
        }
    };

    return <>
        <Head title="Support" />
        <style>
            {`
            @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@700;800&display=swap');
            .support-page { min-height: 100vh; background: #fff; color: #151719; font-family: 'DM Sans', sans-serif; }
            .support-nav { height: 72px; display: flex; align-items: center; justify-content: space-between; max-width: 1070px; padding: 0 24px; margin: auto; }
            .support-brand { display: flex; align-items: center; gap: 11px; color: #ee9b10; font-weight: 700; font-size: 17px; text-decoration: none; }
            .support-mark { display: grid; place-items: center; width: 34px; height: 34px; border-radius: 50%; background: #f4a10d; color: white; font-size: 15px; font-weight: 800; }
            .support-nav-link { border: 0; border-radius: 24px; padding: 11px 21px; background: #ed9a0b; color: white; text-decoration: none; font-size: 13px; font-weight: 700; }
            .support-hero { background: radial-gradient(circle at 72% 24%, #3b454a 0, #181b1d 27%, #101113 65%); min-height: 660px; overflow: hidden; position: relative; }
            .support-hero::before { content: ''; position: absolute; width: 560px; height: 560px; border: 1px solid rgba(247,164,19,.25); border-radius: 50%; right: -115px; top: 55px; box-shadow: 0 0 0 60px rgba(247,164,19,.025), 0 0 0 120px rgba(247,164,19,.018); }
            .support-hero-inner { max-width: 1070px; min-height: 660px; margin: auto; padding: 70px 24px; display: grid; grid-template-columns: 1fr 1fr; align-items: center; gap: 80px; position: relative; z-index: 1; }
            .support-eyebrow { display: inline-block; padding: 6px 12px; border-radius: 15px; background: #fff1d8; color: #e79008; font-size: 10px; letter-spacing: .07em; font-weight: 800; }
            .support-title { max-width: 500px; margin: 18px 0; color: white; font-family: 'Manrope', sans-serif; font-size: clamp(38px, 5vw, 60px); line-height: 1.05; letter-spacing: -.05em; }
            .support-title span { color: #f5a516; }
            .support-copy { max-width: 420px; color: #bcc0c4; line-height: 1.7; font-size: 16px; }
            .support-points { display: grid; grid-template-columns: 1fr 1fr; gap: 13px; margin-top: 33px; max-width: 430px; }
            .support-point { color: #f7f7f7; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 9px; }
            .support-point i { display: grid; place-items: center; width: 21px; height: 21px; background: #f3a10f; border-radius: 50%; color: #121212; font-style: normal; font-size: 12px; }
            .support-card { padding: 31px; border: 1px solid rgba(255,255,255,.15); background: rgba(255,255,255,.97); border-radius: 17px; box-shadow: 0 24px 55px rgba(0,0,0,.32); }
            .support-card h2 { margin: 0 0 4px; font-family: 'Manrope', sans-serif; font-size: 25px; letter-spacing: -.04em; }
            .support-card p { margin: 0 0 22px; color: #6d7278; font-size: 13px; }
            .support-card label { display: block; margin: 0 0 7px; font-size: 12px; font-weight: 700; }
            .support-card input, .support-card textarea { width: 100%; box-sizing: border-box; border: 1px solid #e5e7eb; border-radius: 7px; background: #fafafa; padding: 12px 13px; font: inherit; font-size: 13px; outline: none; }
            .support-card input:focus, .support-card textarea:focus { border-color: #ef9d0d; box-shadow: 0 0 0 3px rgba(239,157,13,.13); }
            .support-field { margin-bottom: 14px; }.support-card textarea { min-height: 102px; resize: vertical; }
            .support-error { display: block; color: #cf2d35; margin-top: 4px; font-size: 11px; }.support-alert { padding: 10px 12px; border-radius: 7px; margin: 0 0 15px; font-size: 13px; }.support-alert.success { background: #e9f8ec; color: #197238; }.support-alert.error { background: #ffebed; color: #be2732; }
            .support-submit { width: 100%; border: 0; border-radius: 25px; padding: 13px; background: #f2a00e; color: #171717; cursor: pointer; font-size: 14px; font-weight: 800; }.support-submit:disabled { opacity: .65; cursor: wait; }
            .support-info { max-width: 1070px; margin: auto; display: grid; grid-template-columns: repeat(3, 1fr); gap: 50px; padding: 65px 24px; }.support-info h3 { font-family: 'Manrope', sans-serif; font-size: 17px; margin: 0 0 8px; }.support-info p { color: #73777c; font-size: 14px; line-height: 1.6; margin: 0; }.support-info-icon { color: #ed9b0c; font-size: 26px; display: block; margin-bottom: 13px; }
            .support-footer { background: #191a1b; color: white; }.support-footer-inner { max-width: 1070px; margin: auto; padding: 43px 24px 23px; display: flex; justify-content: space-between; align-items: flex-start; gap: 30px; }.support-footer p, .support-footer-desc { max-width: 345px; color: #b7b9bc; line-height: 1.6; font-size: 13px; }.support-footer h4 { margin: 0 0 10px; font-size: 14px; overflow-wrap: break-word; }.support-footer a { color: #d5d7d9; display: block; margin: 7px 0; text-decoration: none; font-size: 13px; }.support-copyright { max-width: 1070px; margin: auto; padding: 17px 24px; border-top: 1px solid #393a3c; color: #999da1; font-size: 12px; text-align: center; }
            @media (max-width: 760px) { .support-nav { height: 64px; }.support-hero-inner { grid-template-columns: 1fr; gap: 38px; padding: 55px 24px; }.support-hero { min-height: auto; }.support-title { font-size: 43px; }.support-card { padding: 23px; }.support-info { grid-template-columns: 1fr; gap: 28px; padding: 45px 24px; }.support-footer-inner { display: grid; grid-template-columns: 1fr 1fr; }.support-footer-inner > :first-child { grid-column: 1 / -1; } }
        `}
        </style>
        <div className="support-page">

            <header className="support-nav">
                <a href="https://joinrootsandroutes.com" className="support-brand">
                    {setting?.light_logo || setting?.dark_logo ? (
                        <>
                            <img src={`/${setting.light_logo || setting.dark_logo}`} alt={setting?.site_name || 'Logo'} style={{ height: '34px', width: 'auto' }} />
                            {setting?.site_name || 'Roots And Routes'}
                        </>
                    ) : (
                        <><span className="support-mark">{setting?.site_name ? setting.site_name.charAt(0) : 'R'}</span>{setting?.site_name || 'Roots And Routes'}</>
                    )}</a>
                <a href="/support" className="support-nav-link">Need Help?</a>
            </header>

            <section className="support-hero">
                <div className="support-hero-inner"><div>
                    <span className="support-eyebrow">WE ARE HERE TO HELP</span>
                    <h1 className="support-title">{page?.title || 'Your Journey, Supported.'}</h1>
                    <div className="support-copy" dangerouslySetInnerHTML={{ __html: page?.content || 'Tell us what happened and our support team will help you get back to exploring meaningful places, stories, and routes.' }} /><div className="support-points"><div className="support-point"><i>✓</i>Personal support</div><div className="support-point"><i>✓</i>Simple follow-up</div><div className="support-point"><i>✓</i>Email responses</div><div className="support-point"><i>✓</i>Travel app assistance</div></div></div>

                    <div className="support-card">
                        <h2>Send a request</h2>
                        <p>Complete the form and we will reply to your email.</p>

                        {notice &&
                            <div className={`support-alert ${Object.keys(errors).length ? 'error' : 'success'}`}>{notice}
                            </div>
                        }


                        <form onSubmit={submit}>

                            <div className="support-field">
                                <label>Your name</label>
                                <input required value={form.name} onChange={(event) => setField('name', event.target.value)} />
                                {errors.name?.[0] && <small className="support-error">{errors.name[0]}</small>}
                            </div>

                            <div className="support-field">
                                <label>Email address</label>
                                <input required type="email" value={form.email} onChange={(event) => setField('email', event.target.value)} />
                                {errors.email?.[0] && <small className="support-error">{errors.email[0]}</small>}
                            </div>

                            <div className="support-field">
                                <label>What can we help with?</label>
                                <input required value={form.subject} onChange={(event) => setField('subject', event.target.value)} />{errors.subject?.[0] && <small className="support-error">{errors.subject[0]}</small>}
                            </div>
                            <div className="support-field"><label>Tell us more</label>
                                <textarea required value={form.message} onChange={(event) => setField('message', event.target.value)} />
                                {errors.message?.[0] && <small className="support-error">{errors.message[0]}</small>}
                            </div>

                            <button className="support-submit" disabled={sending}>{sending ? 'Submitting request...' : 'Submit support request'}</button>
                        </form>

                    </div>
                </div>
            </section>

            <section className="support-info"><div><span className="support-info-icon">✦</span><h3>Share the details</h3><p>Let us know what you were trying to do and what went wrong.</p></div><div><span className="support-info-icon">✉</span><h3>Get an email reply</h3><p>Our team will respond directly to the email address you provide.</p></div><div><span className="support-info-icon">✓</span><h3>Keep exploring</h3><p>We will help you make the most of your {setting?.site_name || 'Roots And Routes'} experience.</p></div></section>
            <footer className="support-footer"><div className="support-footer-inner"><div><a href="https://joinrootsandroutes.com" className="support-brand">{setting?.dark_logo || setting?.light_logo ? <><img src={`/${setting.dark_logo || setting.light_logo}`} alt={setting?.site_name || 'Logo'} style={{ height: '34px', width: 'auto' }} />{setting?.site_name || 'Roots And Routes'}</> : <><span className="support-mark">{setting?.site_name ? setting.site_name.charAt(0) : 'R'}</span>{setting?.site_name || 'Roots And Routes'}</>}</a><div className="support-footer-desc" dangerouslySetInnerHTML={{ __html: setting?.footer_text || 'Discover better places to live, travel with purpose, and connect through the stories that shape each destination.' }} /></div><div><h4>Quick Links</h4><a href="https://joinrootsandroutes.com">Home</a><a href="/support">Support</a><a href="/privacy-policy">Privacy Policy</a></div><div><h4>Get In Touch</h4><a href={`mailto:${setting?.site_email || 'support@example.com'}`}>{setting?.site_email || 'support@example.com'}</a><a href="/terms-and-conditions">Terms & Conditions</a></div></div><div className="support-copyright">{setting?.copyright_text || `© ${new Date().getFullYear()} ${setting?.site_name || 'Roots And Routes'}. All rights reserved.`}</div></footer>
        </div>
    </>
}
