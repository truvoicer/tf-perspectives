// packages/truvoicer/tf-perspectives/resources/js/pages/auth.tsx
import { faEnvelope, faEye, faEyeSlash, faLock, faShoePrints, faUser } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { Link, useForm } from '@inertiajs/react';
import { useFacebook, useGoogle } from '@truvoicer/tru-fetcher-core-ui';
import type { FormEvent } from 'react';
import { useEffect, useRef, useState } from 'react';
import { Button, Col, Form, InputGroup, Row } from 'react-bootstrap';
import { route } from 'ziggy-js';
import Layout from '../components/perspectives/layout';

interface AuthProps {
    mode: 'login' | 'register';
    canResetPassword?: boolean;
    status?: string;
}

interface AuthForm {
    first_name: string;
    last_name: string;
    email: string;
    password: string;
    password_confirmation: string;
    remember: boolean;
    auth_context: string;
    [key: string]: string | boolean;
}

export default function Auth({ mode, status }: AuthProps) {
    const googleBtnRef = useRef<HTMLDivElement | null>(null);
    const { ready: googleReady, renderButton, initOneTap, setPostHandlers: setGooglePostHandlers } = useGoogle();
    const { ready: fbReady, login: facebookLogin, setPostHandlers: setFbPostHandlers } = useFacebook();
    const isRegister = mode === 'register';
    const [showPassword, setShowPassword] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm<AuthForm>({
        first_name: '',
        last_name: '',
        email: '',
        password: '',
        password_confirmation: '',
        remember: false,
        auth_context: 'perspectives',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        if (isRegister) {
            post(route('register'), {
                onFinish: () => reset('password', 'password_confirmation'),
            });
        } else {
            post(route('login'), {
                onFinish: () => reset('password'),
            });
        }
    };

    useEffect(() => {
        setFbPostHandlers({
            onSuccess: () => {
                console.log('Facebook login successful');
            },
            onError: (errors) => {
                console.error('Facebook login errors', { errors });
            },
        });

        setGooglePostHandlers({
            onSuccess: () => {
                console.log('Google login successful');
            },
            onError: (errors) => {
                console.error('Google login errors', { errors });
            },
        });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    useEffect(() => {
        if (!googleReady) return;

        initOneTap({ autoPrompt: false });

        if (googleBtnRef.current) {
            googleBtnRef.current.innerHTML = '';
            renderButton(googleBtnRef.current);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [googleReady]);
    return (
        <Layout>
            <Row className="justify-content-center">
                <Col md={8} lg={6} xl={5}>
                    <div className="tf-auth-card">
                        <div className="tf-auth-icon">
                            <FontAwesomeIcon icon={faShoePrints} />
                        </div>

                        <h1>{isRegister ? 'Join the circle' : 'Welcome back'}</h1>
                        <p className="text-muted">
                            {isRegister ? 'Share your view. Step into other people’s.' : 'Sign in to keep the conversation going.'}
                        </p>

                        {status && <div className="alert alert-info small">{status}</div>}

                        <Form onSubmit={submit}>
                            {isRegister && (
                                <Row className="g-2">
                                    <Col md={6}>
                                        <Form.Group className="mb-3">
                                            <Form.Label>First name</Form.Label>
                                            <InputGroup>
                                                <InputGroup.Text>
                                                    <FontAwesomeIcon icon={faUser} />
                                                </InputGroup.Text>
                                                <Form.Control
                                                    value={data.first_name}
                                                    onChange={(e) => setData('first_name', e.target.value)}
                                                    required
                                                    maxLength={60}
                                                    autoComplete="given-name"
                                                    isInvalid={Boolean(errors.first_name)}
                                                />
                                                <Form.Control.Feedback type="invalid">{errors.first_name}</Form.Control.Feedback>
                                            </InputGroup>
                                        </Form.Group>
                                    </Col>

                                    <Col md={6}>
                                        <Form.Group className="mb-3">
                                            <Form.Label>Last name</Form.Label>
                                            <InputGroup>
                                                <InputGroup.Text>
                                                    <FontAwesomeIcon icon={faUser} />
                                                </InputGroup.Text>
                                                <Form.Control
                                                    value={data.last_name}
                                                    onChange={(e) => setData('last_name', e.target.value)}
                                                    required
                                                    maxLength={60}
                                                    autoComplete="family-name"
                                                    isInvalid={Boolean(errors.last_name)}
                                                />
                                                <Form.Control.Feedback type="invalid">{errors.last_name}</Form.Control.Feedback>
                                            </InputGroup>
                                        </Form.Group>
                                    </Col>
                                </Row>
                            )}

                            <Form.Group className="mb-3">
                                <Form.Label>Email</Form.Label>
                                <InputGroup>
                                    <InputGroup.Text>
                                        <FontAwesomeIcon icon={faEnvelope} />
                                    </InputGroup.Text>
                                    <Form.Control
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        required
                                        autoComplete="email"
                                        isInvalid={Boolean(errors.email)}
                                    />
                                    <Form.Control.Feedback type="invalid">{errors.email}</Form.Control.Feedback>
                                </InputGroup>
                            </Form.Group>

                            <Form.Group className="mb-3">
                                <Form.Label>Password</Form.Label>
                                <InputGroup>
                                    <InputGroup.Text>
                                        <FontAwesomeIcon icon={faLock} />
                                    </InputGroup.Text>
                                    <Form.Control
                                        type={showPassword ? 'text' : 'password'}
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        required
                                        minLength={8}
                                        autoComplete={isRegister ? 'new-password' : 'current-password'}
                                        isInvalid={Boolean(errors.password)}
                                    />
                                    <Button
                                        variant="outline-secondary"
                                        type="button"
                                        onClick={() => setShowPassword((v) => !v)}
                                        tabIndex={-1}
                                        title={showPassword ? 'Hide password' : 'Show password'}
                                    >
                                        <FontAwesomeIcon icon={showPassword ? faEyeSlash : faEye} />
                                    </Button>
                                    <Form.Control.Feedback type="invalid">{errors.password}</Form.Control.Feedback>
                                </InputGroup>
                            </Form.Group>

                            {isRegister && (
                                <Form.Group className="mb-3">
                                    <Form.Label>Confirm password</Form.Label>
                                    <InputGroup>
                                        <InputGroup.Text>
                                            <FontAwesomeIcon icon={faLock} />
                                        </InputGroup.Text>
                                        <Form.Control
                                            type={showPassword ? 'text' : 'password'}
                                            value={data.password_confirmation}
                                            onChange={(e) => setData('password_confirmation', e.target.value)}
                                            required
                                            minLength={8}
                                            autoComplete="new-password"
                                            isInvalid={Boolean(errors.password_confirmation)}
                                        />
                                        <Form.Control.Feedback type="invalid">{errors.password_confirmation}</Form.Control.Feedback>
                                    </InputGroup>
                                </Form.Group>
                            )}

                            {!isRegister && (
                                <>
                                    <div className="d-flex justify-content-between mb-3">
                                        <Form.Check type="checkbox" label="Remember me" />
                                        <Link href={route('perspectives.password.request')} className="text-decoration-none small">
                                            Forgot password?
                                        </Link>
                                    </div>
                                    <Form.Group className="mb-3">
                                        <Form.Check
                                            type="checkbox"
                                            id="remember"
                                            label="Keep me signed in"
                                            checked={data.remember}
                                            onChange={(e) => setData('remember', e.target.checked)}
                                        />
                                    </Form.Group>
                                </>
                            )}

                            {errors.email && !isRegister && <div className="alert alert-danger small py-2">{errors.email}</div>}

                            <Button type="submit" variant="primary" size="lg" className="w-100" disabled={processing}>
                                {processing ? 'One moment…' : isRegister ? 'Join' : 'Sign in'}
                            </Button>

                            <div className="mt-4">
                                <div className="small mb-3 text-center text-muted">Or continue with</div>
                                <div className="d-flex justify-content-center gap-2">

                                    <div className="flex items-center justify-center">
                                        <div ref={googleBtnRef} />
                                    </div>

                                    {/* Facebook */}
                                    <button
                                        type="button"
                                        disabled={!fbReady}
                                        onClick={() => facebookLogin()}
                                        className="inline-flex items-center justify-center gap-2 rounded-xl border border-[var(--card-border)] bg-[var(--bg-primary)] px-4 py-2.5 text-sm text-[var(--text-primary)] transition hover:border-[var(--app-primary-color)]/40 hover:bg-[var(--app-primary-color)]/5 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <svg className="h-5 w-5 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                        </svg>
                                        Facebook
                                    </button>
                                </div>
                            </div>
                        </Form>

                        <div className="tf-auth-foot">
                            {isRegister ? (
                                <>
                                    Already have an account? <a href={route('perspectives.login')}>Sign in</a>
                                </>
                            ) : (
                                <>
                                    New here? <a href={route('perspectives.register')}>Create an account</a>
                                </>
                            )}
                        </div>
                    </div>
                </Col>
            </Row>
        </Layout>
    );
}
