export function Stars({ value, label }: { value: number; label?: string }) {
  const rounded = Math.round(value * 2) / 2;
  return (
    <span className="stars" role="img" aria-label={label ?? `${value.toFixed(1)} out of 5`}>
      {[1, 2, 3, 4, 5].map((i) => {
        const fill = rounded >= i ? 1 : rounded >= i - 0.5 ? 0.5 : 0;
        return (
          <svg key={i} viewBox="0 0 20 20" aria-hidden="true">
            <defs>
              <linearGradient id={`half-${i}`}>
                <stop offset="50%" stopColor="currentColor" />
                <stop offset="50%" stopColor="#cdd6cf" />
              </linearGradient>
            </defs>
            <path
              d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 14.9l-5.2 2.7 1-5.8L1.5 7.7l5.9-.9z"
              fill={fill === 1 ? "currentColor" : fill === 0.5 ? `url(#half-${i})` : "#cdd6cf"}
            />
          </svg>
        );
      })}
    </span>
  );
}

export function RatingInline({ avg, count }: { avg: number; count: number }) {
  if (!count) return <span className="no-reviews">No verified reviews yet</span>;
  return (
    <span className="rating">
      <Stars value={avg} />
      {avg.toFixed(1)}
      <span className="muted" style={{ fontWeight: 400 }}>
        ({count} verified {count === 1 ? "review" : "reviews"})
      </span>
    </span>
  );
}
