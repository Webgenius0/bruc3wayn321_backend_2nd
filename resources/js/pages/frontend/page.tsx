import { Head, Link, usePage } from '@inertiajs/react';

type PublicPage = {
    title: string;
    slug: string;
    content: string | null;
};

type Props = {
    page: PublicPage;
};

export default function Page({ page }: Props) {
    const { setting } = usePage().props as any;
    const content = page.content?.trim();

    return (
        <>
            <Head title={page.title} />
            <style>
                {`
                @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@700;800&display=swap');
                .support-page { min-height: 100vh; background: #fff; color: #151719; font-family: 'DM Sans', sans-serif; }
                .support-nav { height: 72px; display: flex; align-items: center; justify-content: space-between; max-width: 1070px; padding: 0 24px; margin: auto; }
                .support-brand { display: flex; align-items: center; gap: 11px; color: #ee9b10; font-weight: 700; font-size: 17px; text-decoration: none; }
                .support-mark { display: grid; place-items: center; width: 34px; height: 34px; border-radius: 50%; background: #f4a10d; color: white; font-size: 15px; font-weight: 800; }
                .support-nav-link { border: 0; border-radius: 24px; padding: 11px 21px; background: #ed9a0b; color: white; text-decoration: none; font-size: 13px; font-weight: 700; }
                .support-hero { background: radial-gradient(circle at 72% 24%, #3b454a 0, #181b1d 27%, #101113 65%); padding-top: 60px; padding-bottom: 60px; overflow: hidden; position: relative; }
                .support-hero::before { content: ''; position: absolute; width: 560px; height: 560px; border: 1px solid rgba(247,164,19,.25); border-radius: 50%; right: -115px; top: 55px; box-shadow: 0 0 0 60px rgba(247,164,19,.025), 0 0 0 120px rgba(247,164,19,.018); }
                .support-hero-inner { max-width: 1070px; margin: auto; padding: 0 24px; position: relative; z-index: 1; text-align: center; }
                .support-title { margin: 0; color: white; font-family: 'Manrope', sans-serif; font-size: clamp(38px, 5vw, 60px); line-height: 1.05; letter-spacing: -.05em; }
                
                .page-content-wrapper { max-width: 800px; margin: auto; padding: 65px 24px; }
                .page-content { color: #4a4d50; line-height: 1.8; font-size: 16px; font-family: 'DM Sans', sans-serif; }
                .page-content h1, .page-content h2, .page-content h3 { font-family: 'Manrope', sans-serif; color: #151719; margin-top: 2em; margin-bottom: 0.5em; letter-spacing: -.02em; }
                .page-content h1:first-child, .page-content h2:first-child, .page-content h3:first-child { margin-top: 0; }
                .page-content h1 { font-size: 32px; }
                .page-content h2 { font-size: 26px; }
                .page-content h3 { font-size: 20px; }
                .page-content p { margin-bottom: 1.5em; }
                .page-content ul, .page-content ol { margin-bottom: 1.5em; padding-left: 20px; }
                .page-content li { margin-bottom: 0.5em; }
                .page-content a { color: #ed9b0c; text-decoration: none; }
                .page-content a:hover { text-decoration: underline; }
                
                .support-footer { background: #191a1b; color: white; }
                .support-footer-inner { max-width: 1070px; margin: auto; padding: 43px 24px 23px; display: flex; justify-content: space-between; align-items: flex-start; gap: 30px; }
                .support-footer p, .support-footer-desc { max-width: 345px; color: #b7b9bc; line-height: 1.6; font-size: 13px;     overflow-wrap: break-word;}
                .support-footer h4 { margin: 0 0 10px; font-size: 14px; }
                .support-footer a { color: #d5d7d9; display: block; margin: 7px 0; text-decoration: none; font-size: 13px; }
                .support-copyright { max-width: 1070px; margin: auto; padding: 17px 24px; border-top: 1px solid #393a3c; color: #999da1; font-size: 12px; text-align: center; }
                @media (max-width: 760px) { 
                    .support-nav { height: 64px; }
                    .support-hero-inner { padding: 0 24px; }
                    .support-title { font-size: 43px; }
                    .page-content-wrapper { padding: 45px 24px; }
                    .support-footer-inner { display: grid; grid-template-columns: 1fr 1fr; }
                    .support-footer-inner > :first-child { grid-column: 1 / -1; } 
                }
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
                        )}
                    </a>
                    <Link href="/support" className="support-nav-link">Need Help?</Link>
                </header>

                <section className="support-hero">
                    <div className="support-hero-inner">
                        <h1 className="support-title">{page.title}</h1>
                    </div>
                </section>

                <section className="page-content-wrapper">
                    <div className="page-content">
                        {content ? (
                            <div dangerouslySetInnerHTML={{ __html: content }} />
                        ) : (
                            <p>Content will be available soon.</p>
                        )}
                    </div>
                </section>

                <footer className="support-footer">
                    <div className="support-footer-inner">
                        <div>
                            <a href="https://joinrootsandroutes.com" className="support-brand">
                                {setting?.dark_logo || setting?.light_logo ? (
                                    <>
                                        <img src={`/${setting.dark_logo || setting.light_logo}`} alt={setting?.site_name || 'Logo'} style={{ height: '34px', width: 'auto' }} />
                                        {setting?.site_name || 'Roots And Routes'}
                                    </>
                                ) : (
                                    <><span className="support-mark">{setting?.site_name ? setting.site_name.charAt(0) : 'R'}</span>{setting?.site_name || 'Roots And Routes'}</>
                                )}
                            </a>
                            <div className="support-footer-desc" dangerouslySetInnerHTML={{ __html: setting?.footer_text || 'Discover better places to live, travel with purpose, and connect through the stories that shape each destination.' }} />
                        </div>
                        <div>
                            <h4>Quick Links</h4>
                            <a href="https://joinrootsandroutes.com">Home</a>
                            <Link href="/support">Support</Link>
                            <Link href="/privacy-policy">Privacy Policy</Link>
                        </div>
                        <div>
                            <h4>Get In Touch</h4>
                            <a href={`mailto:${setting?.site_email || 'support@example.com'}`}>{setting?.site_email || 'support@example.com'}</a>
                            <Link href="/terms-and-conditions">Terms & Conditions</Link>
                        </div>
                    </div>
                    <div className="support-copyright">{setting?.copyright_text || `© ${new Date().getFullYear()} ${setting?.site_name || 'Roots And Routes'}. All rights reserved.`}</div>
                </footer>
            </div>
        </>
    );
}
