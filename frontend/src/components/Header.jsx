import { useState } from 'react';

export default function Header({ compact = false }) {
  const [logoFailed, setLogoFailed] = useState(false);

  return (
    <header className={`brand-header ${compact ? 'brand-header--compact' : ''}`}>
      {!logoFailed && (
        <img
          src="/logo.png"
          alt="Séminaire"
          className="brand-logo"
          onError={() => setLogoFailed(true)}
        />
      )}
      {logoFailed && <span className="brand-fallback">QUIZ SÉRIES</span>}
    </header>
  );
}
