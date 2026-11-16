import 'package:flutter_test/flutter_test.dart';
import 'package:siis_mobile/main.dart';

void main() {
  testWidgets('SiisApp initializes smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(const SiisApp());
    expect(find.byType(SiisApp), findsOneWidget);
  });
}
