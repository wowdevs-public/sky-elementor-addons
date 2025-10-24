import React, { useState, useEffect, Suspense, lazy } from "react";
import { __ } from "@wordpress/i18n";
import Sticky from 'react-sticky-el';

import Nav from "./components/includes/Nav";
import Footer from "./components/includes/Footer";
import DashboardLayout from "./components/DashboardLayout";

const Welcome = lazy(() => import("./components/Welcome"));
const Widgets = lazy(() => import("./components/Widgets"));
const Extensions = lazy(() => import("./components/Extensions"));
const ThirdParty = lazy(() => import("./components/ThirdParty"));
const API = lazy(() => import("./components/Api"));
const License = lazy(() => import("./components/License"));
const GetPro = lazy(() => import("./components/includes/GetPro"));
const FAQs = lazy(() => import("./components/FAQs"));
const HelpCenter = React.lazy(() => import("./components/includes/HelpCenter"));
// const Reviews = lazy(() => import("./components/Reviews"));
const ThemeBuilder = lazy(() => import("./components/ThemeBuilder"));

import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  faWifi,
  faQuestion,
  faHeadset,
  faObjectGroup,
  faSwatchbook,
  faFolder,
  faKey,
  faFolderOpen,
  faBars,
  faChevronLeft,
  faChevronRight,
  faXmark,
} from "@fortawesome/free-solid-svg-icons";

const DashboardData = [
  {
    label: __('Dashboard', 'sky-elementor-addons'),
    value: "dashboard",
    icon: <FontAwesomeIcon icon={faWifi} className="h-5 w-5" />,
    desc: <Welcome />,
  },
  {
    label: __('Widgets', 'sky-elementor-addons'),
    value: "widgets",
    icon: <FontAwesomeIcon icon={faObjectGroup} className="h-5 w-5" />,
    desc: <Widgets />,
  },
  {
    label: __('Extensions', 'sky-elementor-addons'),
    value: "extensions",
    icon: <FontAwesomeIcon icon={faSwatchbook} className="h-5 w-5" />,
    desc: <Extensions />,
  },
  {
    label: __('3rd Party', 'sky-elementor-addons'),
    value: "thirdparty",
    icon: <FontAwesomeIcon icon={faFolder} className="h-5 w-5" />,
    desc: <ThirdParty />,
  },
  // {
  //   label: __('API Data', 'sky-elementor-addons'),
  //   value: "api",
  //   icon: <FontAwesomeIcon icon={faFolder} className="h-5 w-5" />,
  //   desc: <API />,
  // },
  {
    label: __('Theme Builder', 'sky-elementor-addons'),
    value: "theme_builder",
    icon: <FontAwesomeIcon icon={faFolderOpen} className="h-5 w-5" />,
    desc: <ThemeBuilder />,
  },
  {
    label: SkyAddonsConfig.pro_init ? __('License', 'sky-elementor-addons') : __('Get Pro', 'sky-elementor-addons'),
    value: "license",
    icon: <FontAwesomeIcon icon={faKey} className="h-5 w-5" />,
    desc: SkyAddonsConfig.pro_init ? <License /> : <GetPro />,
  },
  {
    label: __('FAQs', 'sky-elementor-addons'),
    value: "faqs",
    icon: <FontAwesomeIcon icon={faQuestion} className="h-5 w-5" />,
    desc: <FAQs />,
  },
  {
    label: __('Support', 'sky-elementor-addons'),
    value: "docs",
    icon: <FontAwesomeIcon icon={faHeadset} className="h-5 w-5" />,
    desc: <HelpCenter />,
  },
];
const Dashboard = () => {
  const pluginNameWithoutSpace = SkyAddonsConfig.plugin_name.replace(/\s+/g, '');

  const [activeTab, setActiveTab] = useState(() => {
    const hash = window.location.hash.replace('#', '');
    if (hash && DashboardData.some(item => item.value === hash)) {
      return hash;
    }
    return localStorage.getItem(pluginNameWithoutSpace + "ActiveTab") || "dashboard";
  });

  const [isSidebarOpen, setIsSidebarOpen] = useState(() => {
    const savedState = localStorage.getItem(pluginNameWithoutSpace + "SidebarOpen");
    return savedState !== null ? savedState === "true" : true;
  });

  const [isLargeScreen, setIsLargeScreen] = useState(window.innerWidth >= 1280);


  // Helper function to check if a tab value is valid
  const isValidTab = (tab) => {
    return DashboardData.some(item => item.value === tab);
  };

  // Listen for hash changes in URL
  useEffect(() => {
    const handleHashChange = () => {
      const hash = window.location.hash.replace('#', '');
      // Only update if hash corresponds to a valid tab
      if (hash && isValidTab(hash)) {
        setActiveTab(hash);
      }
    };

    window.addEventListener('hashchange', handleHashChange);
    // Check hash on initial load
    handleHashChange();

    return () => {
      window.removeEventListener('hashchange', handleHashChange);
    };
  }, []);

  useEffect(() => {
    const handleResize = () => {
      setIsLargeScreen(window.innerWidth >= 1280);
    };

    handleResize();
    window.addEventListener("resize", handleResize);

    return () => {
      window.removeEventListener("resize", handleResize);
    };
  }, []);

  useEffect(() => {
    localStorage.setItem(pluginNameWithoutSpace + "ActiveTab", activeTab);
    window.location.hash = activeTab;
  }, [activeTab]);

  useEffect(() => {
    localStorage.setItem(pluginNameWithoutSpace + "SidebarOpen", isSidebarOpen);
  }, [isSidebarOpen]);

  const handleTabClick = (value) => {
    setActiveTab(value);
  };

  const toggleSidebar = () => {
    setIsSidebarOpen(!isSidebarOpen);
  };

  return (
    <>
      <Nav />
      <DashboardLayout
        data={DashboardData}
        activeTab={activeTab}
        onTabClick={handleTabClick}
        isSidebarOpen={isSidebarOpen}
        toggleSidebar={toggleSidebar}
        isLargeScreen={isLargeScreen}
      />
      <Footer />
    </>
  );
};

export default Dashboard;
