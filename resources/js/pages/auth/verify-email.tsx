import { Link, useForm } from '@inertiajs/react';
import { Button } from 'react-bootstrap';
import Layout from '../../components/perspectives/layout';
import { route } from 'ziggy-js';

export default function VerifyEmail({ status }: { status?: string }) {
    const { post, processing } = useForm();

    const resend = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('perspectives.verification.send'));
    };

    return (
        <Layout>
            <div className="tf-auth-card mx-auto" style={{ maxWidth: '500px' }}>
                <h1>Verify your email</h1>
                <p className="text-muted mb-4">
                    Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
                </p>

                {status === 'verification-link-sent' && (
                    <div className="alert alert-success mb-4">
                        A new verification link has been sent to the email address you provided during registration.
                    </div>
                )}

                <form onSubmit={resend}>
                    <Button variant="primary" type="submit" disabled={processing} className="w-100">
                        Resend Verification Email
                    </Button>
                </form>

                <div className="mt-4">
                    <Link href={route('logout')} method="post" as="button" className="btn btn-link text-muted">
                        Log Out
                    </Link>
                </div>
            </div>
        </Layout>
    );
}
