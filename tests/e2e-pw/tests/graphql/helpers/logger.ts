// import { TestInfo } from '@playwright/test';

// export function logLocalesInfo(
//   testInfo: TestInfo,
//   locales: any[]
// ) {
//   const codes = locales.map(l => l.node.code);
//   const rtlCount = locales.filter(
//     l => l.node.direction === 'rtl'
//   ).length;

//   testInfo.log(`Total locales: ${locales.length}`);
//   testInfo.log(`Locale codes: ${codes.join(', ')}`);
//   testInfo.log(`RTL locales count: ${rtlCount}`);
// }

// export function logResponseTime(
//   testInfo: TestInfo,
//   label: string,
//   timeMs: number
// ) {
//   testInfo.log(`${label} response time: ${timeMs} ms`);
// }
