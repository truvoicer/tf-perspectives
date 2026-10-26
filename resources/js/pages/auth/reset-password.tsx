import { useForm } from '@inertiajs/react';
import { Button, Form } from 'react-bootstrap';
import Layout from '../../components/perspectives/layout';
import { route } from 'ziggy-js';

export default function ResetPassword({ token, email }: { token: string, email: string }) {
    const { data, setData, post, processing, errors } = useForm({
        token,
        email: email || '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('perspectives.password.update'));
    };

    return (
        <Layout>
            <div className="tf-auth-card mx-auto" style={{ maxWidth: '450px' }}>
                <h1>Choose a new password</h1>
                <Form onSubmit={submit} className="mt-4">
                    <Form.Group className="mb-3 text-start">
                        <Form.Label>Email</Form.Label>
                        <Form.Control
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            isInvalid={!!errors.email}
                            required
                        />
                        <Form.Control.Feedback type="invalid">{errors.email}</Form.Control.Feedback>
                    </Form.Group>
                    <Form.Group className="mb-3 text-start">
                        <Form.Label>New Password</Form.Label>
                        <Form.Control
                            type="password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            isInvalid={!!errors.password}
                            required
                        />
                        <Form.Control.Feedback type="invalid">{errors.password}</Form.Control.Feedback>
                    </Form.Group>
                    <Form.Group className="mb-4 text-start">
                        <Form.Label>Confirm New Password</Form.Label>
                        <Form.Control
                            type="password"
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            isInvalid={!!errors.password_confirmation}
                            required
                        />
                    </Form.Group>
                    <Button variant="primary" type="submit" disabled={processing} className="w-100">
                        Reset Password
                    </Button>
                </Form>
            </div>
        </Layout>
    );
}
