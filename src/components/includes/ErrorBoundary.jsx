import React from "react";
import { __ } from "@wordpress/i18n";

class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false };
  }

  static getDerivedStateFromError(error) {
    return { hasError: true };
  }

  componentDidCatch(error, errorInfo) {
    console.error("ErrorBoundary caught an error:", error, errorInfo);
  }

  render() {
    if (this.state.hasError) {
      return (
        <div className="p-4 text-center bg-red-100 text-red-700 rounded-lg">
          <h2>{__('Something went wrong.', 'sky-elementor-addons')}</h2>
          <p>{__('Please try refreshing the page.', 'sky-elementor-addons')}</p>
        </div>
      );
    }

    return this.props.children;
  }
}

export default ErrorBoundary;
