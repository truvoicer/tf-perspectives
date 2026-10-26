import { useForm } from '@inertiajs/react';
import { Button, Form } from 'react-bootstrap';
import Layout from '../../components/perspectives/layout';
import { route } from 'ziggy-js';

export default function ForgotPassword({ status }: { status?: string }) {
    const { data, setData, post, processing, errors } = useForm({ email: '' });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('perspectives.password.email'));
    };

    return (
        <Layout>
            <div className="tf-auth-card mx-auto" style={{ maxWidth: '450px' }}>
                <h1>Reset Password</h1>
                <p className="text-muted mb-4">
                    Enter your email and we'll send you a link to reset your password.
                </p>

                {status && <div className="alert alert-success">{status}</div>}

                <Form onSubmit={submit}>
                    <Form.Group className="mb-3 text-start">
                        <Form.Label>Email address</Form.Label>
                        <Form.Control
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            isInvalid={!!errors.email}
                            required
                        />
                        <Form.Control.Feedback type="invalid">{errors.email}</Form.Control.Feedback>
                    </Form.Group>
                    <Button variant="primary" type="submit" disabled={processing} className="w-100">
                        Send Reset Link
                    </Button>
                </Form>
            </div>
        </Layout>
    );
}
