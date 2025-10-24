import React from 'react';
import { __ } from "@wordpress/i18n";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  faHeadset,
  faLayerGroup,
  faBriefcase,
  faChartLine,
  faUserFriends,
  faUserShield,
  faArrowRight,
  faWandMagicSparkles,
  faCode,
  faMoon,
  faTable,
  faWindowMaximize,
  faTag,
  faVideo,
  faQrcode,
  faBars,
  faScroll,
  faCompass,
  faParachuteBox,
  faInfoCircle,
  faCheck,
  faTimes,
  faCrown,
  faBuilding,
  faUser
} from "@fortawesome/free-solid-svg-icons";


const additionalFeatures = [
  {
    icon: faInfoCircle,
    title: __('Tooltip', 'sky-elementor-addons'),
    description: __('Add informative tooltips to any element with customizable styling.', 'sky-elementor-addons'),
  },
  {
    icon: faWandMagicSparkles,
    title: __('Theme Builder', 'sky-elementor-addons'),
    description: __('Create custom headers, footers, and page templates with full control.', 'sky-elementor-addons'),
  },
  {
    icon: faScroll,
    title: __('Smooth Scroll', 'sky-elementor-addons'),
    description: __('Enhance user experience with buttery-smooth page scrolling.', 'sky-elementor-addons'),
  },
  {
    icon: faLayerGroup,
    title: __('Premium Templates', 'sky-elementor-addons'),
    description: __('Access our exclusive library of professionally designed templates.', 'sky-elementor-addons'),
  },
  {
    icon: faCompass,
    title: __('Breadcrumbs', 'sky-elementor-addons'),
    description: __('Improve site navigation and SEO with customizable breadcrumbs.', 'sky-elementor-addons'),
  },
  {
    icon: faMoon,
    title: __('Dark Mode', 'sky-elementor-addons'),
    description: __('Add togglable dark mode to your site with just a few clicks.', 'sky-elementor-addons'),
  },
  {
    icon: faWindowMaximize,
    title: __('Iframe', 'sky-elementor-addons'),
    description: __('Embed external content seamlessly with advanced iframe options.', 'sky-elementor-addons'),
  },
  {
    icon: faTable,
    title: __('Data Table', 'sky-elementor-addons'),
    description: __('Create responsive, sortable, and filterable tables effortlessly.', 'sky-elementor-addons'),
  },
  {
    icon: faBars,
    title: __('Nav Menu', 'sky-elementor-addons'),
    description: __('Design stunning navigation menus with countless style options.', 'sky-elementor-addons'),
  },
  {
    icon: faWindowMaximize,
    title: __('Off-canvas', 'sky-elementor-addons'),
    description: __('Create slidable content panels for menus, forms, and more.', 'sky-elementor-addons'),
  },
  {
    icon: faTable,
    title: __('Pricing Table', 'sky-elementor-addons'),
    description: __('Showcase your pricing plans with beautiful, conversion-optimized tables.', 'sky-elementor-addons'),
  },
  {
    icon: faQrcode,
    title: __('QR Code', 'sky-elementor-addons'),
    description: __('Generate custom QR codes to connect offline and online experiences.', 'sky-elementor-addons'),
  },
  {
    icon: faTag,
    title: __('Tags Cloud', 'sky-elementor-addons'),
    description: __('Display your content tags in an interactive, visually appealing cloud.', 'sky-elementor-addons'),
  },
  {
    icon: faVideo,
    title: __('Hover Video', 'sky-elementor-addons'),
    description: __('Create interactive videos that play on hover with customizable controls.', 'sky-elementor-addons'),
  },
  {
    icon: faVideo,
    title: __('Video Gallery', 'sky-elementor-addons'),
    description: __('Build beautiful video galleries with filters and custom layouts.', 'sky-elementor-addons'),
  },
  {
    icon: faParachuteBox,
    title: __('And Much More!', 'sky-elementor-addons'),
    description: __('Particles, Display Controls, Custom Scripts, and many more premium features.', 'sky-elementor-addons'),
  },
];

const pricingFeatures = [
  __('All Widgets Access', 'sky-elementor-addons'),
  __('All Features Access', 'sky-elementor-addons'),
  __('Premium Templates', 'sky-elementor-addons'),
  __('Theme Builder', 'sky-elementor-addons'),
  __('WooCommerce', 'sky-elementor-addons'),
  __('White Label Option', 'sky-elementor-addons'),
  __('Priority Support', 'sky-elementor-addons'),
  __('Lifetime Updates', 'sky-elementor-addons'),
  __('Multiple Site License', 'sky-elementor-addons'),
  __('Agency Features', 'sky-elementor-addons'),
];

const pricingPlans = [
  {
    id: 'free',
    name: __('FREE', 'sky-elementor-addons'),
    price: __('$0', 'sky-elementor-addons'),
    interval: '',
    description: __('Limited Features', 'sky-elementor-addons'),
    icon: faUser,
    iconColor: 'text-gray-400',
    popular: false,
    buttonText: __('Current Plan', 'sky-elementor-addons'),
    buttonLink: '#',
    buttonColor: 'bg-gray-400',
    buttonGradient: false,
    smallText: __('Basic features for personal projects', 'sky-elementor-addons'),
    trial: false,
    features: [
      { text: __('Limited Widgets', 'sky-elementor-addons'), included: true },
      { text: __('Limited', 'sky-elementor-addons'), included: true },
      { text: '', included: false },
      { text: __('Limited', 'sky-elementor-addons'), included: true },
      { text: '', included: false },
      { text: '', included: false },
      { text: '', included: false },
      { text: '', included: false },
      { text: '', included: false },
      { text: '', included: false },
    ]
  },
  {
    id: 'pro',
    name: __('PRO', 'sky-elementor-addons'),
    price: __('$29', 'sky-elementor-addons'),
    interval: __('/year', 'sky-elementor-addons'),
    description: __('Single Site License', 'sky-elementor-addons'),
    icon: faCrown,
    iconColor: 'text-purple-500',
    popular: true,
    buttonText: __('Get Started', 'sky-elementor-addons'),
    buttonLink: 'https://skyaddons.com/pricing/',
    buttonColor: '',
    buttonGradient: 'bg-gradient-to-br from-pink-500 to-purple-700 hover:bg-gradient-to-bl',
    smallText: __('14-day free trial.', 'sky-elementor-addons'),
    trial: true,
    features: [
      { text: __('All Widgets', 'sky-elementor-addons'), included: true },
      { text: '', included: true },
      { text: '', included: true },
      { text: '', included: true },
      { text: '', included: true },
      { text: '', included: false },
      { text: '', included: true },
      { text: '', included: true },
      { text: '', included: false },
      { text: '', included: false },
    ]
  },
  {
    id: 'agency',
    name: __('AGENCY', 'sky-elementor-addons'),
    price: __('$149', 'sky-elementor-addons'),
    interval: __('/year', 'sky-elementor-addons'),
    description: __('Unlimited Sites', 'sky-elementor-addons'),
    icon: faBuilding,
    iconColor: 'text-gray-600 dark:text-gray-300',
    popular: false,
    buttonText: __('Get Started', 'sky-elementor-addons'),
    buttonLink: 'https://skyaddons.com/pricing/',
    buttonColor: '',
    buttonGradient: 'bg-gradient-to-br from-purple-600 to-blue-500 hover:bg-gradient-to-bl',
    smallText: __('14-day free trial. Perfect for agencies and professionals.', 'sky-elementor-addons'),
    trial: true,
    features: [
      { text: __('All Widgets', 'sky-elementor-addons'), included: true },
      { text: '', included: true },
      { text: '', included: true },
      { text: '', included: true },
      { text: '', included: true },
      { text: '', included: true },
      { text: '', included: true },
      { text: '', included: true },
      { text: '', included: true },
      { text: '', included: true },
    ]
  }
];

const GetPro = () => (
  <section className="bg-white dark:bg-gray-900">
    <div className="py-8 px-4 mx-auto max-w-screen-xl sm:py-16 lg:px-6">
      {/* More Premium Features Section */}
      <div className="max-w-screen-md mb-8 lg:mb-12 mx-auto text-center">
        <h2 className="mb-4 text-3xl tracking-tight font-extrabold text-gray-900 dark:text-white">
          {__('Unlock These Premium Features Today', 'sky-elementor-addons')}
        </h2>
        <p className="text-gray-500 sm:text-lg dark:text-gray-400">
          {__('Take your website to the next level with our extensive collection of premium widgets and features.', 'sky-elementor-addons')}
        </p>
      </div>
      <div className="space-y-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 md:gap-8 md:space-y-0">
        {additionalFeatures.map((feature, index) => (
          <div key={`additional-${index}`} className="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-300">
            <div className="flex items-center mb-2">
              <FontAwesomeIcon icon={feature.icon} className="w-5 h-5 text-primary-600 mr-2 dark:text-primary-300" />
              <h3 className="text-lg font-bold dark:text-white">{feature.title}</h3>
            </div>
            <p className="text-sm text-gray-500 dark:text-gray-400">{feature.description}</p>
          </div>
        ))}
      </div>
    </div>

    {/* Pricing Table Section */}
    <section className="text-gray-700 body-font overflow-hidden  border-gray-200 dark:border-gray-700 dark:text-gray-300">
      <div className="container px-5 py-6 mx-auto flex flex-wrap">
        <div className="flex w-full mb-10 flex-wrap">
          <h2 className="text-3xl font-bold text-center w-full mb-2">{__('Choose Your Plan', 'sky-elementor-addons')}</h2>
          <p className="text-center w-full text-gray-500 dark:text-gray-400 mb-5">
            {__('Select the perfect plan for your needs with our flexible pricing options', 'sky-elementor-addons')}
          </p>
        </div>

        <div className="lg:w-1/4 mt-48 hidden lg:block">
          <div className="mt-px border-t border-gray-300 dark:border-gray-700 border-b border-l rounded-tl-lg rounded-bl-lg overflow-hidden">
            {pricingFeatures.map((feature, index) => (
              <p key={index} className={`${index % 2 === 0 ? 'bg-gray-100 dark:bg-gray-800' : ''} text-gray-900 dark:text-gray-200 h-12 text-center px-4 flex items-center justify-start -mt-px`}>
                {feature}
              </p>
            ))}
          </div>
        </div>

        <div className="flex lg:w-3/4 w-full flex-wrap lg:border border-gray-300 dark:border-gray-700 rounded-lg">
          {pricingPlans.map((plan, planIndex) => (
            <div
              key={plan.id}
              className={`lg:w-1/3 ${planIndex === 1 ? 'lg:-mt-px' : 'lg:mt-px'} w-full mb-10 lg:mb-0 ${plan.popular
                  ? 'border-2 rounded-lg border-purple-500 relative'
                  : 'border-2 border-gray-300 dark:border-gray-700 lg:border-none rounded-lg lg:rounded-none'
                }`}
            >
              {plan.popular && (
                <span className="bg-purple-500 text-white px-3 py-1 tracking-widest text-xs absolute right-0 top-0 rounded-bl">
                  {__('POPULAR', 'sky-elementor-addons')}
                </span>
              )}
              <div className="px-2 text-center h-48 flex flex-col items-center justify-center">
                <FontAwesomeIcon icon={plan.icon} className={`w-10 h-10 mb-2 ${plan.iconColor}`} />
                <h3 className={`tracking-widest ${plan.id === 'pro' ? 'text-purple-500' : 'text-gray-500 dark:text-gray-400'}`}>{plan.name}</h3>
                <h2 className="text-5xl text-gray-900 dark:text-white font-medium flex items-center justify-center leading-none mb-4 mt-2">
                  {plan.price}
                  {plan.interval && <span className="text-gray-600 dark:text-gray-400 text-base ml-1">{plan.interval}</span>}
                </h2>
                <span className="text-sm text-gray-600 dark:text-gray-400">{plan.description}</span>
              </div>

              {/* Feature rows */}
              {pricingFeatures.map((feature, featureIndex) => (
                <p key={`${plan.id}-feature-${featureIndex}`} className={`${featureIndex % 2 === 0 ? 'bg-gray-100 dark:bg-gray-800' : ''} text-gray-600 dark:text-gray-400 h-12 text-center px-2 flex items-center -mt-px justify-center ${featureIndex === 0 ? 'border-t border-gray-300 dark:border-gray-700' : ''}`}>
                  {plan.features[featureIndex].included ? (
                    <>
                      <FontAwesomeIcon icon={faCheck} className="text-green-500 mr-2" />
                      {plan.features[featureIndex].text}
                    </>
                  ) : (
                    <FontAwesomeIcon icon={faTimes} className="text-red-500 mr-2" />
                  )}
                </p>
              ))}

              <div className={`p-6 text-center ${plan.id !== 'pro' ? 'border-t border-gray-300 dark:border-gray-700' : ''}`}>
                {plan.id === 'free' ? (
                  <button className={`flex items-center mt-auto text-white ${plan.buttonColor} border-0 py-3 px-4 w-full focus:outline-none rounded justify-center`}>
                    {plan.buttonText}
                  </button>
                ) : (
                  <a href={plan.buttonLink} target="_blank" rel="noopener noreferrer"
                    className={`flex items-center mt-auto text-white ${plan.buttonGradient} border-0 py-3 px-4 w-full focus:outline-none hover:shadow-lg transition-all duration-300 rounded justify-center`}>
                    {plan.buttonText}
                    <FontAwesomeIcon icon={faArrowRight} className="w-4 h-4 ml-2" />
                  </a>
                )}
                <p className="text-xs text-gray-500 dark:text-gray-400 mt-3">
                  {plan.smallText}
                </p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>

    <div className="py-8 text-center">
      <div className="mb-4 text-gray-700 dark:text-gray-300">
        {__('Join 10,000+ web professionals who already upgraded', 'sky-elementor-addons')}
      </div>
      <a target="_blank" href="https://skyaddons.com/pricing/" rel="noopener noreferrer"
        className="text-white focus:ring-4 focus:outline-none focus:ring-[#3b5998]/50 font-medium rounded-lg text-lg px-10 py-5 text-center inline-flex items-center dark:focus:ring-[#3b5998]/55 bg-gradient-to-br from-pink-500 to-purple-700 hover:bg-gradient-to-bl hover:shadow-lg transition-all duration-300">
        <FontAwesomeIcon icon={faArrowRight} className="w-5 h-5 me-2" />
        {__('Start Your 14-Day Free Trial', 'sky-elementor-addons')}
      </a>
      <div className="mt-4 text-sm text-gray-500 dark:text-gray-400">
        {__('Full access to all premium features.', 'sky-elementor-addons')}
      </div>
    </div>
  </section>
);

export default GetPro;
